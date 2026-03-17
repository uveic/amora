<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

readonly class BackofficeApiControllerStoreMediaForCollectionSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $collectionMediaId = null,
        ?string $html = null,
        ?string $caption = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['collectionMediaId'] = is_null($collectionMediaId)
            ? null
            : $collectionMediaId;

        $responseData['html'] = is_null($html)
            ? null
            : $html;

        $responseData['caption'] = is_null($caption)
            ? null
            : $caption;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        [$output, $contentType] = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
