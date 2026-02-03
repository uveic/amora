<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class PublicApiControllerLogMessageSuccessResponse extends Response
{
    public function __construct()
    {
        // Required parameters
        $responseData = [
            'ok' => true,
        ];

        [$output, $contentType] = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
