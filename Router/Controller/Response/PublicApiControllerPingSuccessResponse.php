<?php
namespace Amora\Router\Controller\Response;

use Amora\Core\Model\Response;

class PublicApiControllerPingSuccessResponse extends Response
{
    public function __construct()
    {
        // Required parameters
        $responseData = [
            'ok' => true,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
