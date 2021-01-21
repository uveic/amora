<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class BackofficeApiControllerUpdateArticleSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $articleId = null,
        ?string $uri = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['articleId'] = is_null($articleId)
            ? null
            : $articleId;

        $responseData['uri'] = is_null($uri)
            ? null
            : $uri;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
