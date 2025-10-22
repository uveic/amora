<?php

namespace Amora\Core\Module\Mailer\App\Api\Lettermint;

use Throwable;
use Amora\Core\Util\Logger;
use Amora\Core\Module\Mailer\App\Api\ApiClientAbstract;
use Amora\Core\Module\Mailer\App\Api\ApiResponse;

class ApiClient extends ApiClientAbstract
{
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

    public function sendEmail(
        string $logPrefix,
        string $jsonData
    ): ApiResponse {
        $requestHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
            'x-lettermint-token: ' . $this->apiKey,
        ];

        try {
            $res = $this->apiPostCall(
                $logPrefix,
                $this->baseApiUrl,
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
