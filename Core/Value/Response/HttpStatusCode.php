<?php

namespace Amora\Core\Value\Response;

enum HttpStatusCode: string
{
    case HTTP_200_OK = 'HTTP/1.1 200 OK';
    case HTTP_307_TEMPORARY_REDIRECT = 'HTTP/1.1 307 Temporary Redirect';
    case HTTP_400_BAD_REQUEST = 'HTTP/1.1 400 Bad Request';
    case HTTP_401_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
    case HTTP_403_FORBIDDEN = 'HTTP/1.1 403 Forbidden';
    case HTTP_404_NOT_FOUND = 'HTTP/1.1 404 Not Found';
    case HTTP_500_INTERNAL_ERROR = 'HTTP/1.1 500 Internal Server Error';
}
