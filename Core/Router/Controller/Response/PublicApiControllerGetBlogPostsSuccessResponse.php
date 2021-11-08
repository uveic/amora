<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Model\Response;

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
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
