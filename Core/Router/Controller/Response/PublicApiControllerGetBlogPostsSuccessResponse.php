<?php
namespace Amora\Core\Router\Controller\Response;

use Amora\Core\Entity\Response;
use Amora\Core\Entity\HttpStatusCode;

readonly class PublicApiControllerGetBlogPostsSuccessResponse extends Response
{
    public function __construct(bool $success, $blogPosts, $pagination)
    {
        // Required parameters
        $responseData = [
            'success' => $success,
            'blogPosts' => $blogPosts,
            'pagination' => $pagination,
        ];

        [$output, $contentType] = self::getResponseType($responseData);
        parent::__construct($output, $contentType, HttpStatusCode::HTTP_200_OK);
    }
}
