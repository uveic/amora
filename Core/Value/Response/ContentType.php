<?php

namespace Amora\Core\Value\Response;

enum ContentType: string
{
    case JSON = 'application/json';
    case XML = 'application/xml';
    case PLAIN = 'text/plain;charset=UTF-8';
    case HTML = 'text/html;charset=UTF-8';
    case CSV = 'text/csv';
}
