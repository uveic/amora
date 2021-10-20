<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;

class AuthorisedApiControllerSendVerificationEmailSuccessResponse extends Response
{
    public function __construct(bool $success)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
