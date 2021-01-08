<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class BackofficeApiControllerStoreArticleSuccessResponse extends Response
{
    public function __construct(bool $success)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
