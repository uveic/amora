<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class PublicApiControllerGetSessionSuccessResponse extends Response
{
    public function __construct($user = null)
    {
        // Required parameters
        $responseData = [
        ];

        $responseData['user'] = is_null($user)
            ? null
            : $user;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
