<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;

class PublicApiControllerLogErrorSuccessResponse extends Response
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
