<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerStoreArticleSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $articleId = null,
        ?string $articleBackofficePath = null,
        ?string $articleBackofficePathPreview = null,
        ?string $articlePublicUrlHtml = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['articleId'] = is_null($articleId)
            ? null
            : $articleId;

        $responseData['articleBackofficePath'] = is_null($articleBackofficePath)
            ? null
            : $articleBackofficePath;

        $responseData['articleBackofficePathPreview'] = is_null($articleBackofficePathPreview)
            ? null
            : $articleBackofficePathPreview;

        $responseData['articlePublicUrlHtml'] = is_null($articlePublicUrlHtml)
            ? null
            : $articlePublicUrlHtml;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        [$output, $contentType] = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
