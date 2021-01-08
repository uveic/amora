<?php
namespace uve\router\controller\response;

use uve\core\model\Response;

class AuthorisedApiControllerStoreImageSuccessResponse extends Response
{
    public function __construct(
        bool $success,
        array $images,
        ?string $errorMessage = null
    ) {
        // Required parameters
        $responseData = [
            'success' => $success,
            'images' => $images,
        ];

        $responseData['errorMessage'] = is_null($errorMessage)
            ? null
            : $errorMessage;

        list($output, $contentType) = self::getResponseType($responseData);
        parent::__construct($output, $contentType, Response::HTTP_200_OK);
    }
}
