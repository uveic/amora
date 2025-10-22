<?php

namespace Amora\Core\Entity;

use Amora\App\Value\Language;
use Amora\Core\Entity\Response\HtmlResponseData;
use DOMDocument;
use JsonException;
use League\Plates\Engine;
use SimpleXMLElement;
use Amora\Core\Core;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

enum HttpStatusCode: string
{
    case HTTP_200_OK = 'HTTP/1.1 200 OK';
    case HTTP_301_PERMANENT_REDIRECT = 'HTTP/1.1 301 Moved Permanently';
    case HTTP_307_TEMPORARY_REDIRECT = 'HTTP/1.1 307 Temporary Redirect';
    case HTTP_400_BAD_REQUEST = 'HTTP/1.1 400 Bad Request';
    case HTTP_401_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
    case HTTP_403_FORBIDDEN = 'HTTP/1.1 403 Forbidden';
    case HTTP_404_NOT_FOUND = 'HTTP/1.1 404 Not Found';
    case HTTP_500_INTERNAL_ERROR = 'HTTP/1.1 500 Internal Server Error';
}

enum ContentType: string
{
    case JSON = 'application/json';
    case XML = 'application/xml';
    case PLAIN = 'text/plain;charset=UTF-8';
    case HTML = 'text/html;charset=UTF-8';
    case CSV = 'text/csv';
    case PDF = 'application/pdf';
}

class Response
{
    public function __construct(
        public readonly string $output,
        ContentType $contentType,
        HttpStatusCode $httpStatus,
        protected array $headers = [],
        ?string $nonce = null,
    ) {
        $nonce = $nonce ? " 'nonce-" . $nonce . "'" : '';

        $allowedDomains = ["'self'"];
        if (Core::getConfig()->s3Config) {
            $allowedDomains[] = parse_url(Core::getConfig()->s3Config->originEndpoint, PHP_URL_HOST);
            if (Core::getConfig()->s3Config->cdnEndpoint) {
                $allowedDomains[] = parse_url(Core::getConfig()->s3Config->cdnEndpoint, PHP_URL_HOST);
            }
        }

        $allowedUrls = implode(' ', array_merge(["'self'"], Core::getConfig()->allowedUrlsForSrcScript ?? []));
        $connectSrc = 'connect-src ' . $allowedUrls . ';';
        $scriptSrc = 'script-src ' . $allowedUrls . $nonce . ';';
        $defaultSrc = "default-src 'self';";
        if (Core::getConfig()->allowImgSrcData) {
            $allowedDomains[] = 'data: blob:;';
        }
        $imgSrc = 'img-src ' . implode(' ', $allowedDomains) . ';';
        $mediaSrc = 'media-src ' . implode(' ', $allowedDomains) . ';';
        $frameSrc = Core::getConfig()->allowYouTubeIFrame ? "frame-src https://www.youtube-nocookie.com 'self';" : "";
        $styleSrc = "style-src 'self' 'unsafe-inline';";

        $insecureRequests = '';
        if (Core::isRunningInLiveEnv()) {
            $headers[] = 'Strict-Transport-Security: max-age=31536000';
            $insecureRequests = ' upgrade-insecure-requests';
        }

        if (Core::getConfig()->allowedCorsDomains) {
            $headers[] = 'Access-Control-Allow-Origin: ' . implode(', ', Core::getConfig()->allowedCorsDomains);
            $headers[] = 'Access-Control-Allow-Headers: Content-Type';
        }

        // To log content security policy errors:
        // Content-Security-Policy: report-uri /papi/csp;
        $this->headers = array_merge(
            [
                $httpStatus->value,
                "Content-Type: $contentType->value",
                "Cache-Control: private, s-maxage=0, max-age=0, must-revalidate",
                "Content-Security-Policy: $defaultSrc $connectSrc $scriptSrc $imgSrc $mediaSrc $frameSrc $styleSrc" . $insecureRequests,
                "X-Content-Type-Options: nosniff",
                "Referrer-Policy: strict-origin-when-cross-origin",
                "X-Frame-Options: SAMEORIGIN",
            ],
            $headers,
        );
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    ///////////////////////////////////////////////////////////////////////////
    // Static helpers for constructing a response

    public static function createSuccessResponse($payload): Response
    {
        [$output, $contentType] = self::getResponseType($payload);
        return new Response(
            output: $output,
            contentType: $contentType,
            httpStatus: HttpStatusCode::HTTP_200_OK,
        );
    }

    public static function createForbiddenResponse($payload): Response
    {
        [$output, $contentType] = self::getResponseType($payload);
        return new Response(
            output: $output,
            contentType: $contentType,
            httpStatus: HttpStatusCode::HTTP_403_FORBIDDEN,
        );
    }

    public static function createHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData,
        HttpStatusCode $httpStatusCode = HttpStatusCode::HTTP_200_OK,
    ): Response {
        $slashPos = strrpos($template, '/');
        if ($slashPos === false) {
            return self::createErrorResponse('Invalid template path: ' . $template);
        }

        $templateName = substr($template, $slashPos + 1);
        $partialTemplatePath = substr($template, 0, $slashPos);

        $fullTemplatePath = Core::getPathRoot() . '/view/' . $partialTemplatePath;
        $view = new Engine($fullTemplatePath);
        $html = $view->render(
            name: $templateName,
            data: ['responseData' => $responseData],
        );

        return new Response(
            output: $html,
            contentType: ContentType::HTML,
            httpStatus: $httpStatusCode,
            nonce: $responseData->nonce,
        );
    }

    private static function createDownloadResponse(
        string $localPath,
        string $fileName,
        ContentType $contentType,
    ): Response {
        return new Response(
            output: file_get_contents($localPath),
            contentType: $contentType,
            httpStatus: HttpStatusCode::HTTP_200_OK,
            headers: [
                'Cache-Control: public',
                'Content-Transfer-Encoding: Binary',
                'Content-Length:' . filesize($localPath),
                "Content-Disposition: attachment; filename=$fileName"
            ],
        );
    }

    public static function createCsvDownloadResponse(string $localPath, string $fileName): Response
    {
        return self::createDownloadResponse(
            localPath: $localPath,
            fileName: $fileName,
            contentType: ContentType::CSV,
        );
    }

    public static function createTextDownloadResponse(string $localPath, string $fileName): Response
    {
        return self::createDownloadResponse(
            localPath: $localPath,
            fileName: $fileName,
            contentType: ContentType::PLAIN,
        );
    }

    public static function createPdfResponse(
        string $url,
        string $localPath,
        string $fileName,
    ): Response {
        return new Response(
            output: '',
            contentType: ContentType::PDF,
            httpStatus: HttpStatusCode::HTTP_200_OK,
            headers: [
                'Cache-Control: public',
                'Content-Transfer-Encoding: Binary',
                'Content-Length:' . filesize($localPath),
                "Content-Disposition: attachment; filename=$fileName",
                'Location: ' . $url,
            ],
        );
    }

    public static function createRedirectResponse(string $url): Response
    {
        return new Response(
            output: '',
            contentType: ContentType::HTML,
            httpStatus: HttpStatusCode::HTTP_307_TEMPORARY_REDIRECT,
            headers: [
                'Location: ' . $url
            ],
        );
    }

    public static function createPermanentRedirectResponse(string $url): Response
    {
        return new Response(
            output: '',
            contentType: ContentType::HTML,
            httpStatus: HttpStatusCode::HTTP_301_PERMANENT_REDIRECT,
            headers: [
                'Location: ' . $url
            ],
        );
    }

    public static function createNotFoundResponse(
        Request $request,
        ?HtmlResponseDataAbstract $responseData = null,
    ): Response {
        return self::createHtmlResponse(
            template: 'app/public/404',
            responseData: $responseData ?? new HtmlResponseData($request),
            httpStatusCode: HttpStatusCode::HTTP_404_NOT_FOUND,
        );
    }

    public static function createJsonNotFoundResponse(): Response
    {
        return new Response(
            output: json_encode(['success' => false]),
            contentType: ContentType::JSON,
            httpStatus: HttpStatusCode::HTTP_404_NOT_FOUND,
        );
    }

    public static function createUnauthorisedRedirectLoginResponse(Language $language): Response
    {
        return self::createRedirectResponse(
            url: UrlBuilderUtil::buildPublicLoginUrl($language),
        );
    }

    public static function createUnauthorisedHtmlResponse(
        Request $request,
        ?HtmlResponseDataAbstract $responseData = null,
    ): Response {
        return self::createHtmlResponse(
            template: 'app/public/403',
            responseData: $responseData ?? new HtmlResponseData($request),
            httpStatusCode: HttpStatusCode::HTTP_403_FORBIDDEN,
        );
    }

    public static function createUnauthorizedJsonResponse(): Response
    {
        $response = [
            'success' => false,
            'errorMessage' => 'Whoops! Not authorised...'
        ];

        return new Response(
            output: json_encode($response),
            contentType: ContentType::JSON,
            httpStatus: HttpStatusCode::HTTP_403_FORBIDDEN,
        );
    }

    public static function createBadRequestResponse($payload): Response
    {
        [$output, $contentType] = self::getResponseType($payload);
        return new Response(
            output: $output,
            contentType: $contentType,
            httpStatus: HttpStatusCode::HTTP_400_BAD_REQUEST,
        );
    }

    public static function createErrorResponse(
        string $msg = 'There was an unexpected error :('
    ): Response {
        return new Response(
            output: $msg,
            contentType: ContentType::HTML,
            httpStatus: HttpStatusCode::HTTP_500_INTERNAL_ERROR,
        );
    }

    public static function createSuccessXmlResponse(string $payload): Response
    {
        return new Response(
            output: $payload,
            contentType: ContentType::XML,
            httpStatus: HttpStatusCode::HTTP_200_OK,
        );
    }

    public static function createSuccessJsonResponse(string $payload): Response
    {
        return new Response(
            output: $payload,
            contentType: ContentType::JSON,
            httpStatus: HttpStatusCode::HTTP_200_OK,
        );
    }

    protected static function getResponseType($payload): array
    {
        if ($payload instanceof SimpleXMLElement) {
            return [$payload->asXML(), ContentType::XML];
        }

        if ($payload instanceof DOMDocument) {
            return [$payload->saveXML(), ContentType::XML];
        }

        if (is_array($payload) || is_object($payload)) {
            try {
                return [json_encode($payload, JSON_THROW_ON_ERROR), ContentType::JSON];
            } catch (JsonException $e) {
                Core::getDefaultLogger()->logException($e);
                return [];
            }
        }

        return [$payload, ContentType::PLAIN];
    }
}
