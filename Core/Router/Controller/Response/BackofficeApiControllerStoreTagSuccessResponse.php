<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class BackofficeApiControllerStoreTagSuccessResponse extends Response
{
    public function __construct(bool $success, ?int $id = null)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
        ];

        $responseData['id'] = is_null($id)
            ? null
            : $id;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
