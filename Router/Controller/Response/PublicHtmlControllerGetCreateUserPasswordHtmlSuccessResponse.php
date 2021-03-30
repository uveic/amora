<?php
namespace Amora\Router\Controller\Response;

use Amora\Core\Model\Response;

class PublicHtmlControllerGetCreateUserPasswordHtmlSuccessResponse extends Response
{
    public function __construct($responseData)
    {
        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
