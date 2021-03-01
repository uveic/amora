<?php

namespace Amora\Router;

use Amora\Core\Model\Request;
use Amora\Core\Model\Response;

abstract class AbstractController
{
    abstract function route(Request $request): Response;

    protected function validatePathType($var, $type): bool
    {
        if ($type == "int" && filter_var($var, FILTER_VALIDATE_INT) === false) {
            return false;
        }

        return true;
    }

    protected function pathParamsMatcher(
        array $pathTemplate,
        array $pathParts,
        array $pathTypes = []
    ): bool {
        if (count($pathTemplate) !== count($pathParts)) {
            return false;
        }

        foreach ($pathParts as $index => $part) {
            $templatePart = trim(urldecode($pathTemplate[$index]));

            if (substr($templatePart, 0, 1) === '{' && substr($templatePart, -1, 1) === '}') {
                if ($this->validatePathType($part, $pathTypes[$index]) === false) {
                    return false;
                }
            } elseif ($pathParts[$index] !== $templatePart) {
                return false;
            }
        }

        return true;
    }

    protected function getPathParams(array $pathTemplate, array $pathParts): array
    {
        $pathParams = array();

        foreach ($pathParts as $index => $part) {
            $templatePart = trim(urldecode($pathTemplate[$index]));

            if (substr($templatePart, 0, 1) === '{' && substr($templatePart, -1, 1) === '}') {
                $pathParams[substr($templatePart, 1, -1)] = $part;
            }
        }

        return $pathParams;
    }
}
