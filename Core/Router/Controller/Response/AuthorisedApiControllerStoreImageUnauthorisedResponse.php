<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Model\HttpStatusCode;

class AuthorisedApiControllerStoreImageUnauthorisedResponse extends Response
{
    public function __construct()
    {
        // Required parameters
        $responseData = [
            'success' => false,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_401_UNAUTHORIZED);
    }
}
