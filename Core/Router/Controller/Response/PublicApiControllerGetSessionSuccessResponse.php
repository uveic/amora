<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class PublicApiControllerGetSessionSuccessResponse extends Response
{
    public function __construct($user = null, $session = null)
    {
        // Required parameters
        $responseData = [
        ];

        $responseData['user'] = is_null($user)
            ? null
            : $user;

        $responseData['session'] = is_null($session)
            ? null
            : $session;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
