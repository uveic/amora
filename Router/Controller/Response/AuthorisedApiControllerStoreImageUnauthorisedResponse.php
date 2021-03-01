<?php
namespace Amora\Router\Controller\Response;

use Amora\Core\Model\Response;

class AuthorisedApiControllerStoreImageUnauthorisedResponse extends Response
{
    public function __construct()
    {
        // Required parameters
        $responseData = [
            'success' => false,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_401_UNAUTHORIZED);
    }
}
