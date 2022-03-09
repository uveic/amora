<?php

namespace Amora\Core\Model;

use Amora\App\Value\Language;
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

    public static function createHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData,
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
            httpStatus: HttpStatusCode::HTTP_200_OK,
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

    public static function createUnauthorisedRedirectLoginResponse(Language $language): Response
    {
        return Response::createRedirectResponse(
            url: UrlBuilderUtil::buildPublicLoginUrl($language),
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
