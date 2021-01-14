<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class BackofficeHtmlControllerGetUsersAdminPageSuccessResponse extends Response
{
    public function __construct($responseData)
    {
        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}