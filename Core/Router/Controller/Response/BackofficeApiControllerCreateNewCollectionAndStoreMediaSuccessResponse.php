<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerCreateNewCollectionAndStoreMediaSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $collectionId = null,
        ?int $collectionMediaId = null,
        ?string $html = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['collectionId'] = is_null($collectionId)
            ? null
            : $collectionId;

        $responseData['collectionMediaId'] = is_null($collectionMediaId)
            ? null
            : $collectionMediaId;

        $responseData['html'] = is_null($html)
            ? null
            : $html;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        [$output, $contentType] = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
