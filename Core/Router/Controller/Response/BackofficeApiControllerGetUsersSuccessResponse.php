<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Model\HttpStatusCode;

class BackofficeApiControllerGetUsersSuccessResponse extends Response
{
    public function __construct(bool $success, array $users)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'users' => $users,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
