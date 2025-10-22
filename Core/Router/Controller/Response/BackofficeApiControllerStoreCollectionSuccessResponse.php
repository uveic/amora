<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerStoreCollectionSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $newCollectionId = null,
        ?string $html = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['newCollectionId'] = is_null($newCollectionId)
            ? null
            : $newCollectionId;

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
