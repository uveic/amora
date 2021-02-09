<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

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
