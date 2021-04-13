<?php
namespace Amora\Router\Controller\Response;

use Amora\Core\Model\Response;

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
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
