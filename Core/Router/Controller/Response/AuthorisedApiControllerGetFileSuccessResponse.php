<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Model\HttpStatusCode;

class AuthorisedApiControllerGetFileSuccessResponse extends Response
{
    public function __construct(bool $success, $file)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'file' => $file,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
