<?php

namespace Amora\Core\Module\Mailer\App\Api\Sendgrid;

use Throwable;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Mailer\App\Api\ApiClientAbstract;
use Amora\Core\Module\Mailer\App\Api\ApiResponse;

class ApiClient extends ApiClientAbstract
{
    const CONTENT_TYPE_JSON = 'Content-Type: application/json';

    private string $baseApiUrl;
    private string $apiKey;

    public function __construct(
        Logger $logger,
        string $baseApiUrl,
        string $apiKey
    ) {
        parent::__construct($logger);
        $this->baseApiUrl = $baseApiUrl;
        $this->apiKey = $apiKey;
    }

    public function post(
        string $logPrefix,
        string $partialApiUrl,
        string $jsonData
    ): ApiResponse {
        $requestHeaders = [
            self::CONTENT_TYPE_JSON,
            'Authorization: Bearer ' . $this->apiKey
        ];

        try {
            $res = $this->apiPostCall(
                $logPrefix,
                $this->baseApiUrl . $partialApiUrl,
                $jsonData,
                $requestHeaders
            );
        } catch (Throwable $t) {
            $res = new ApiResponse('', 500, true);
            $this->logger->logException($t);
        }

        $this->logger->logInfo($logPrefix . 'API call completed...');

        return $res;
    }
}
