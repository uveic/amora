<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class BackofficeApiControllerStoreUserFailureResponse extends Response
{
    public function __construct(string $errorMessage, ?array $errorInfo = null)
    {
        // Required parameters
        $responseData = [
            'success' => false,
            'errorMessage' => $errorMessage,
        ];

        $responseData['errorInfo'] = is_null($errorInfo)
            ? null
            : $errorInfo;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_400_BAD_REQUEST);
    }
}
