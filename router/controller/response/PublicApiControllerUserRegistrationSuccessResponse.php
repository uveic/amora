<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class PublicApiControllerUserRegistrationSuccessResponse extends Response
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
