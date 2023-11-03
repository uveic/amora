<?php

namespace Amora\Core\Util;

enum Method: string
{
    case POST = 'POST';
    case GET = 'GET';
}

readonly class ApiResponse
{
    public function __construct(
        public string $response,
        public int $responseCode,
        public bool $hasError,
        public ?string $errorMessage = null,
    ) {}
}

class ApiClientUtil
{
    const TIMEOUT_SECONDS = 60;

    public static function post(
        string $url,
        string $data,
        array $headers = [],
        ?Logger $logger = null,
        ?string $logPrefix = null,
        string $userAgent = 'Amora',
    ): ApiResponse {
        return self::apiCall(
            method: Method::POST,
            url: $url,
            data: $data,
            requestHeaders: $headers,
            userAgent: $userAgent,
            logger: $logger,
            logPrefix: $logPrefix,
        );
    }

    public static function get(
        string $url,
        ?string $data = null,
        array $headers = [],
        ?Logger $logger = null,
        ?string $logPrefix = null,
        string $userAgent = 'Amora',
    ): ApiResponse {
        return self::apiCall(
            method: Method::GET,
            url: $url,
            data: $data,
            requestHeaders: $headers,
            userAgent: $userAgent,
            logger: $logger,
            logPrefix: $logPrefix,
        );
    }

    private static function apiCall(
        Method $method,
        string $url,
        ?string $data,
        array $requestHeaders = [],
        string $userAgent = 'Amora',
        ?Logger $logger = null,
        string $logPrefix = '',
    ): ApiResponse {
        $logger?->logInfo($logPrefix . 'Calling API...');

        set_time_limit(self::TIMEOUT_SECONDS);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method->value);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($userAgent) {
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        }

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        if ($requestHeaders) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMessage = curl_error($ch);

        curl_close($ch);

        $logger?->logDebug($logPrefix . 'API Request. URL: ' . $url);
        $logger?->logDebug($logPrefix . 'API Request. Data: ' . $data);
        $logger?->logDebug($logPrefix . 'API Response: ' . $responseCode . ' => ' . $response);

        if ($response === false) {
            $logger?->logError($logPrefix . 'API call error: ' . $errorMessage);
        }

        if ($responseCode < 200 || $responseCode > 299) {
            $logger?->logError($logPrefix . 'API error response code: ' . $responseCode);
            return new ApiResponse($response, $responseCode, true, $errorMessage);
        }

        $logger?->logInfo($logPrefix . 'API call completed...');

        return new ApiResponse(
            response: $response ?: '',
            responseCode: $responseCode,
            hasError: false,
        );
    }
}
