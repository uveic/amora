<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerStoreUserSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $id = null,
        ?string $redirect = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['id'] = is_null($id)
            ? null
            : $id;

        $responseData['redirect'] = is_null($redirect)
            ? null
            : $redirect;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
