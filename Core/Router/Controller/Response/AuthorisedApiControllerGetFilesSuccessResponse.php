<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class AuthorisedApiControllerGetFilesSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        array $files,
        ?string $modalMediaSelectInnerHtml = null,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
            'files' => $files,
        ];

        $responseData['modalMediaSelectInnerHtml'] = is_null($modalMediaSelectInnerHtml)
            ? null
            : $modalMediaSelectInnerHtml;

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        [$output, $contentType] = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
