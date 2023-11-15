<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerStoreAlbumSectionSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?int $newSectionId = null,
        ?string $html = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['newSectionId'] = is_null($newSectionId)
            ? null
            : $newSectionId;

        $responseData['html'] = is_null($html)
            ? null
            : $html;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
