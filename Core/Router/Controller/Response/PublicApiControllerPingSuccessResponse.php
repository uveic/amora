<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Model\HttpStatusCode;

class PublicApiControllerPingSuccessResponse extends Response
{
    public function __construct()
    {
        // Required parameters
        $responseData = [
            'ok' => true,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
