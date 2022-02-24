<?php

namespace Amora\Core\Model;

use DOMDocument;
use League\Plates\Engine;
use SimpleXMLElement;
use Amora\Core\Core;
use Amora\Core\Model\Response\HtmlResponseDataAbstract;
use Amora\Core\Util\UrlBuilderUtil;

enum HttpStatusCode: string
{
    case HTTP_200_OK = 'HTTP/1.1 200 OK';
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
}

class Response
{
    public function __construct(
        public readonly string $output,
        ContentType $contentType,
        HttpStatusCode $httpStatus,
        protected array $headers = []
    ) {
        $this->headers = array_merge(
            [
                $httpStatus->value,
                "Content-Type: $contentType->value",
                "Cache-Control: private, s-maxage=0, max-age=0, must-revalidate, no-store",
            ],
            $headers
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
        list($output, $contentType) = self::getResponseType($payload);
        return new Response(
            output: $output,
            contentType: $contentType,
            httpStatus: HttpStatusCode::HTTP_200_OK,
        );
    }

    public static function createForbiddenResponse($payload): Response
    {
        list($output, $contentType) = self::getResponseType($payload);
        return new Response(
            output: $output,
            contentType: $contentType,
            httpStatus: HttpStatusCode::HTTP_403_FORBIDDEN,
        );
    }

    private static function createHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData,
        bool $isFrontendPrivate = false,
        bool $isBackoffice = false,
    ): Response {
        $templatePath = self::getTemplatePath($isBackoffice, $isFrontendPrivate);
        $view = new Engine($templatePath);
        $html = $view->render($template, ['responseData' => $responseData]);
        return new Response(
            output: $html,
            contentType: ContentType::HTML,
            httpStatus: HttpStatusCode::HTTP_200_OK,
        );
    }

    public static function createFrontendPublicHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData,
    ): Response {
        return self::createHtmlResponse(
            template: $template,
            responseData: $responseData,
            isFrontendPrivate: false,
            isBackoffice: false,
        );
    }

    public static function createFrontendPrivateHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData,
    ): Response {
        return self::createHtmlResponse(
            template: $template,
            responseData: $responseData,
            isFrontendPrivate: true,
        );
    }

    public static function createBackofficeHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData,
    ): Response {
        return self::createHtmlResponse(
            template: $template,
            responseData: $responseData,
            isFrontendPrivate: false,
            isBackoffice: true,
        );
    }

    private static function getTemplatePath(bool $isBackoffice, bool $isFrontendPrivate): string
    {
        if ($isBackoffice) {
            $templatePath = 'backoffice/v1';
        } else {
            $templatePath = 'frontend/v1' . ($isFrontendPrivate ? '/private' : '/public');
        }

        return Core::getPathRoot() . '/view/' . $templatePath;
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
                "Content-Disposition: attachment; filename={$fileName}"
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

    public static function createNotFoundResponse(): Response
    {
        return new Response(
            output: 'Page not found :(',
            contentType: ContentType::PLAIN,
            httpStatus: HttpStatusCode::HTTP_404_NOT_FOUND,
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

    public static function createUnauthorisedRedirectLoginResponse(string $siteLanguage): Response
    {
        return Response::createRedirectResponse(
            url: UrlBuilderUtil::buildPublicLoginUrl($siteLanguage),
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
            httpStatus: HttpStatusCode::HTTP_401_UNAUTHORIZED,
        );
    }

    public static function createBadRequestResponse($payload): Response
    {
        list($output, $contentType) = self::getResponseType($payload);
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

    protected static function getResponseType($payload): array
    {
        if ($payload instanceof SimpleXMLElement) {
            return [$payload->asXML(), ContentType::XML];
        } elseif ($payload instanceof DOMDocument) {
            return [$payload->saveXML(), ContentType::XML];
        } elseif (is_array($payload) || is_object($payload)) {
            return [json_encode($payload), ContentType::JSON];
        }

        return [$payload, ContentType::PLAIN];
    }
}
