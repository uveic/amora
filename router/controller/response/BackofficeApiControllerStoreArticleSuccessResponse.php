<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class BackofficeApiControllerStoreArticleSuccessResponse extends Response
{
    public function __construct(bool $success, ?int $articleId = null)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['articleId'] = is_null($articleId)
            ? null
            : $articleId;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
