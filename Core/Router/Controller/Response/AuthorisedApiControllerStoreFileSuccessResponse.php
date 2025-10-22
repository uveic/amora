<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class AuthorisedApiControllerStoreFileSuccessResponse extends Response
{
    public function __construct(bool $success, $file, ?string $errorMessage = null)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'file' => $file,
        ];

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        [$output, $contentType] = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
