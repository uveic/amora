<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Model\HttpStatusCode;

class BackofficeApiControllerUpdateArticleSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $articleId = null,
        ?string $articleBackofficeUri = null,
        ?string $articlePublicUri = null,
        ?string $errorMessage = null
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

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
