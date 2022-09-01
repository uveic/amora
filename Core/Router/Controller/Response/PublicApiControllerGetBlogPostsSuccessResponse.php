<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

class PublicApiControllerGetBlogPostsSuccessResponse extends Response
{
    public function __construct(bool $success, $blogPosts, $pagination)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'blogPosts' => $blogPosts,
            'pagination' => $pagination,
        ];

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
