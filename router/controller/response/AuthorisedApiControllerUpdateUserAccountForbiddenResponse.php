<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class AuthorisedApiControllerUpdateUserAccountForbiddenResponse extends Response
{
    public function __construct()
    {
        // Required parameters
        $responseData = [
            'success' => false,
            'errorMessage' => 'NOT_AUTHORISED',
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_403_FORBIDDEN);
    }
}
