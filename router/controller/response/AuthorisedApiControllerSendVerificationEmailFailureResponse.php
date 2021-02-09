<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class AuthorisedApiControllerSendVerificationEmailFailureResponse extends Response
{
    public function __construct(?array $errorInfo = null)
    {
        // Required parameters
        $responseData = [
            'success' => false,
        ];

        $responseData['errorInfo'] = is_null($errorInfo)
            ? null
            : $errorInfo;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_400_BAD_REQUEST);
    }
}
