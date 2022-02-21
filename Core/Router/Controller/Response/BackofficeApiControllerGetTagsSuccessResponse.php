<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Value\Response\HttpStatusCode;

class BackofficeApiControllerGetTagsSuccessResponse extends Response
{
    public function __construct(bool $success, array $tags)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'tags' => $tags,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
