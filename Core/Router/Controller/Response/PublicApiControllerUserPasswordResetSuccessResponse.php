<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Value\Response\HttpStatusCode;

class PublicApiControllerUserPasswordResetSuccessResponse extends Response
{
    public function __construct(bool $success, ?string $errorMessage = null)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
