<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Model\HttpStatusCode;

class AuthorisedApiControllerGetNextFileSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        array $files,
        ?array $appearsOn = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
            'files' => $files,
        ];

        $responseData['appearsOn'] = is_null($appearsOn)
            ? null
            : $appearsOn;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
