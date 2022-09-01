<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerDestroyUserFailureResponse extends Response
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
