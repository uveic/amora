<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Model\HttpStatusCode;

class AuthorisedApiControllerGetFileSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        $file = null,
        ?string $errorMessage = null,
        ?array $tags = null,
        ?array $appearsOn = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['file'] = is_null($file)
            ? null
            : $file;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        $responseData['tags'] = is_null($tags)
            ? null
            : $tags;

        $responseData['appearsOn'] = is_null($appearsOn)
            ? null
            : $appearsOn;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
