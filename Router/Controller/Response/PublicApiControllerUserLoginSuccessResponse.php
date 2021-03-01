<?php
namespace Amora\Router\Controller\Response;

use Amora\Core\Model\Response;

class PublicApiControllerUserLoginSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?string $redirect = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['redirect'] = is_null($redirect)
            ? null
            : $redirect;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
