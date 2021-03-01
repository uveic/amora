<?php
namespace Amora\Router\Controller\Response;

use Amora\Core\Model\Response;

class BackofficeApiControllerStoreArticleFailureResponse extends Response
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
