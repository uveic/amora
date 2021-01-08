<?php

namespace uve\core\model;

use DOMDocument;
use League\Plates\Engine;
use SimpleXMLElement;
use uve\core\Core;
use uve\core\model\response\HtmlResponseDataAbstract;

class Response
{
    const HTTP_200_OK = 'HTTP/1.1 200 OK';
    const HTTP_307_TEMPORARY_REDIRECT = 'HTTP/1.1 307 Temporary Redirect';
    const HTTP_400_BAD_REQUEST = 'HTTP/1.1 400 Bad Request';
    const HTTP_401_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
    const HTTP_403_FORBIDDEN = 'HTTP/1.1 403 Forbidden';
    const HTTP_404_NOT_FOUND = 'HTTP/1.1 404 Not Found';
    const HTTP_500_INTERNAL_ERROR = 'HTTP/1.1 500 Internal Server Error';

    const JSON = 'application/json';
    const XML = 'application/xml';
    const PLAIN = 'text/plain;charset=UTF-8';
    const HTML = 'text/html;charset=UTF-8';
    const CSV = 'text/csv';

    protected string $output;
    protected array $headers;

    public function __construct(
        string $output,
        string $contentType,
        string $httpStatus,
        array $headers = []
    ) {
        $this->output = $output;
        $this->headers = array_merge(
            [
                $httpStatus,
                "Content-Type: $contentType",
                "Cache-Control: public"
            ],
            $headers
        );
//                "Cache-Control: max-age=2628000, public"
//                "Cache-Control: no-cache, no-store, must-revalidate",
//                "Pragma: no-cache",
//                "Expires: 0"
    }

    public function getOutput(): string
    {
        return $this->output;
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
        return new Response($output, $contentType, self::HTTP_200_OK);
    }

    public static function createForbiddenResponse($payload): Response
    {
        list($output, $contentType) = self::getResponseType($payload);
        return new Response($output, $contentType, self::HTTP_403_FORBIDDEN);
    }

    private static function createHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData,
        bool $isFrontendPrivate = false,
        bool $isBackoffice = false
    ): Response {
        $templatePath = self::getTemplatePath($isBackoffice, $isFrontendPrivate);
        $view = new Engine($templatePath);
        $html = $view->render($template, ['responseData' => $responseData]);
        return new Response($html, self::HTML, self::HTTP_200_OK);
    }

    public static function createFrontendPublicHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData
    ): Response {
        return self::createHtmlResponse($template, $responseData, false, false);
    }

    public static function createFrontendPrivateHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData
    ): Response {
        return self::createHtmlResponse($template, $responseData, true);
    }

    public static function createBackofficeHtmlResponse(
        string $template,
        HtmlResponseDataAbstract $responseData
    ): Response {
        return self::createHtmlResponse($template, $responseData, false, true);
    }

    private static function getTemplatePath(bool $isBackoffice, bool $isFrontendPrivate): string
    {
        if ($isBackoffice) {
            $templatePath = 'backoffice/' . (Core::getConfigValue('backoffice_template') ?? 'v1');
        } else {
            $templatePath = 'frontend/'
                . (Core::getConfigValue('site_template') ?? 'v1')
                . ($isFrontendPrivate ? '/private' : '/public');
        }

        return Core::getPathRoot() . '/view/' . $templatePath;
    }

    public static function createDownloadResponse(
        string $localPath,
        string $fileName,
        string $contentType
    ): Response {
        return new Response(
            file_get_contents($localPath),
            $contentType,
            self::HTTP_200_OK,
            [
                'Cache-Control: public',
                'Content-Transfer-Encoding: Binary',
                'Content-Length:' . filesize($localPath),
                "Content-Disposition: attachment; filename={$fileName}"
            ]
        );
    }

    public static function createRedirectResponse(string $url): Response
    {
        return new Response(
            '',
            self::HTML,
            self::HTTP_307_TEMPORARY_REDIRECT,
            [
                'Location: ' . $url
            ]
        );
    }

    public static function createNotFoundResponse(): Response
    {
        return new Response('Page not found :(', self::PLAIN, self::HTTP_404_NOT_FOUND);
    }

    public static function createJsonNotFoundResponse(): Response
    {
        $response = [
            'success' => false,
            'error' => 'Endpoint not found :('
        ];

        return new Response(
            json_encode($response),
            self::JSON,
            self::HTTP_404_NOT_FOUND
        );
    }

    public static function createUnauthorisedRedirectLoginResponse(): Response
    {
        return Response::createRedirectResponse('/login');
    }

    public static function createUnauthorizedPlainTextResponse(): Response
    {
        return new Response(
            'Whoops! Not authorised...',
            self::PLAIN,
            self::HTTP_401_UNAUTHORIZED
        );
    }

    public static function createBadRequestResponse($payload): Response
    {
        list($output, $contentType) = self::getResponseType($payload);
        return new Response($output, $contentType, self::HTTP_400_BAD_REQUEST);
    }

    public static function createErrorResponse(
        string $msg = 'There was an unexpected error :('
    ): Response {
        return new Response($msg, self::HTML, self::HTTP_500_INTERNAL_ERROR);
    }

    protected static function getResponseType($payload): array
    {
        if ($payload instanceof SimpleXMLElement) {
            $output = $payload->asXML();
            $contentType = self::XML;
        } elseif ($payload instanceof DOMDocument) {
            $output = $payload->saveXML();
            $contentType = self::XML;
        } elseif (is_array($payload) || is_object($payload)) {
            $output = json_encode($payload);
            $contentType = self::JSON;
        } else {
            $output = $payload;
            $contentType = self::PLAIN;
        }

        return array($output, $contentType);
    }
}
