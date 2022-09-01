<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class AuthorisedApiControllerGetNextFileSuccessResponse extends Response
{
    public function __construct(bool $success, array $files, ?string $errorMessage = null)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'files' => $files,
        ];

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
