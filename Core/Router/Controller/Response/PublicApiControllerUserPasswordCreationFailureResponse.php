<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;
use Amora\Core\Value\Response\HttpStatusCode;

class PublicApiControllerUserPasswordCreationFailureResponse extends Response
{
    public function __construct(?array $errorInfo = null)
    {
        // Required parameters
        $responseData = [
            'success' => false,
        ];

        $responseData['errorInfo'] = is_null($errorInfo)
            ? null
            : $errorInfo;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_400_BAD_REQUEST);
    }
}
