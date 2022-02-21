<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Value\Response\HttpStatusCode;

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
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
