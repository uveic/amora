<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class BackofficeApiControllerCheckArticleUriFailureResponse extends Response
{
    public function __construct(?array $errorInfo = null)
    {
        // Required parameters
        $responseData = [
            'success' => false,
            'errorMessage' => 'INVALID_PARAMETERS',
        ];

        $responseData['errorInfo'] = is_null($errorInfo)
            ? null
            : $errorInfo;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_400_BAD_REQUEST);
    }
}
