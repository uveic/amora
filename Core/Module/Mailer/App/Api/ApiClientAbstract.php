<?php

namespace Amora\Core\Module\Mailer\App\Api;

use Amora\Core\Util\Logger;
use Throwable;

abstract class ApiClientAbstract
{
    private const int TIMEOUT_SECONDS = 60;

    public function __construct(
        protected readonly Logger $logger,
    ) {
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
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMessage = curl_error($ch);

        $this->logger->logDebug($logPrefix . 'API Response: ' . $responseCode . ' => ' . $response);

        if ($response === false) {
            $this->logger->logError($logPrefix . 'API call error: ' . $errorMessage);
        }

        if ($responseCode < 200 || $responseCode > 299) {
            $responseAsArray = null;
            try {
                $responseAsArray = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable $t) {
                $errorMessage = $t->getMessage();
            }

            if (!$errorMessage && $responseAsArray) {
                if (isset($responseAsArray['error']['message']) && is_string($responseAsArray['error']['message'])) {
                    $errorMessage = $responseAsArray['error']['message'];
                } elseif (isset($responseAsArray['errors'][0]['message']) && is_string($responseAsArray['errors'][0]['message'])) {
                    $errorMessage = $responseAsArray['errors'][0]['message'];
                } elseif (isset($responseAsArray['error']) && is_string($responseAsArray['error'])) {
                    $errorMessage = $responseAsArray['error'];
                }
            }

            $this->logger->logError($logPrefix . 'API error response code: ' . $responseCode);
            return new ApiResponse(
                response: $response,
                responseCode: $responseCode,
                hasError: true,
                errorMessage: $errorMessage,
            );
        }

        $this->logger->logInfo($logPrefix . 'API call completed...');

        return new ApiResponse(
            response: $response,
            responseCode: $responseCode,
            hasError: false,
        );
    }
}
