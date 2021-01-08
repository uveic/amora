<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class BackofficeApiControllerStoreUserForbiddenResponse extends Response
{
    public function __construct(?string $errorMessage = null)
    {
        // Required parameters
        $responseData = [
            'success' => false,
        ];

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_403_FORBIDDEN);
    }
}
