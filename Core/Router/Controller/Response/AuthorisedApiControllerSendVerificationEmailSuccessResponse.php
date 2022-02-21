<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Value\Response\HttpStatusCode;

class AuthorisedApiControllerSendVerificationEmailSuccessResponse extends Response
{
    public function __construct(bool $success)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
