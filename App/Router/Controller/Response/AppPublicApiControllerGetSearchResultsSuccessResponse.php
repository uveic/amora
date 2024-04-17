<?php
namespace Amora\App\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class AppPublicApiControllerGetSearchResultsSuccessResponse extends Response
{
    public function __construct(bool $success, array $results)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'results' => $results,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
