<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerUpdateAlbumStatusSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        ?string $publicLinkHtml = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['publicLinkHtml'] = is_null($publicLinkHtml)
            ? null
            : $publicLinkHtml;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
