<?php
namespace Amora\Router\Controller\Response;

use Amora\Core\Model\Response;

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
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
