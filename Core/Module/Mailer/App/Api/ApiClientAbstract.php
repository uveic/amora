<?php

namespace Amora\Core\Module\Mailer\App\Api;

use Amora\Core\Util\Logger;

abstract class ApiClientAbstract
{
    const int TIMEOUT_SECONDS = 60;

    protected Logger $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    abstract public function sendEmail(
        string $logPrefix,
        string $jsonData
    ): ApiResponse;

    protected function apiPostCall(
        string $logPrefix,
        string $url,
        string $data,
        array $headers
    ): ApiResponse {
        return $this->apiCall($logPrefix, 'POST', $url, $data, $headers);
    }

    private function apiCall(
        string $logPrefix,
        string $method,
        string $url,
        string $data,
        array $requestHeaders,
    ): ApiResponse {
        $this->logger->logInfo($logPrefix . 'Calling API (' . $url . ')...');

        set_time_limit(self::TIMEOUT_SECONDS);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMessage = curl_error($ch);

        curl_close($ch);

        $this->logger->logDebug($logPrefix . 'API Response: ' . $responseCode . ' => ' . $response);

        if ($response === false) {
            $this->logger->logError($logPrefix . 'API call error: ' . $errorMessage);
        }

        if ($responseCode < 200 || $responseCode > 299) {
            $this->logger->logError($logPrefix . 'API error response code: ' . $responseCode);
            return new ApiResponse($response, $responseCode, true, $errorMessage);
        }

        $this->logger->logInfo($logPrefix . 'API call completed...');

        return new ApiResponse(
            response: $response,
            responseCode: $responseCode,
            hasError: false,
        );
    }
}
