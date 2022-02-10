<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;

class BackofficeApiControllerUpdateArticleSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $articleId = null,
        ?string $articleBackofficeUri = null,
        ?string $articlePublicUri = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['articleId'] = is_null($articleId)
            ? null
            : $articleId;

        $responseData['articleBackofficeUri'] = is_null($articleBackofficeUri)
            ? null
            : $articleBackofficeUri;

        $responseData['articlePublicUri'] = is_null($articlePublicUri)
            ? null
            : $articlePublicUri;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
