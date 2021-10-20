<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;

class PublicApiControllerUserPasswordCreationSuccessResponse extends Response
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
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
