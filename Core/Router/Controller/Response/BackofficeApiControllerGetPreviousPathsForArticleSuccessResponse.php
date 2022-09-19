<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerGetPreviousPathsForArticleSuccessResponse extends Response
{
    public function __construct(bool $success, $paths, ?string $errorMessage = null)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'paths' => $paths,
        ];

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
