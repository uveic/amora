const util = require('./lib/util');

module.exports = function generate(jsonFileName, operations, allResponseClassNames, classPrefix) {
  return util.flatten(renderMain(jsonFileName, operations, allResponseClassNames, classPrefix)).join('\n');
};

function renderMain(jsonFileName, operations, allResponseClassNames, classPrefix) {
  return [
    `<?php

namespace Amora\\${classPrefix}\\Router;

use Amora\\Core\\Core;
use Amora\\Core\\Entity\\Request;
use Amora\\Core\\Entity\\Response;
use Amora\\Core\\Router\\AbstractController;
use Amora\\Core\\Router\\RouterCore;
use Amora\\Core\\Util\\StringUtil;
use Throwable;
`,
    renderAbstractClass(jsonFileName, operations, allResponseClassNames, classPrefix),
    ``
  ];
}

function renderAbstractClass(jsonFileName, operations, allResponseClassNames, classPrefix) {
  const lineToPrint = allResponseClassNames.map(r => {
    return `        require_once Core::getPathRoot() . '/${classPrefix}/Router/Controller/Response/${r}.php';`;
  }).join('\n');

  return [
    `abstract class ${jsonFileName}ControllerAbstract extends AbstractController
{
    public function __construct()
    {
${lineToPrint}
    }

    abstract protected function authenticate(Request $request): bool;`,
    operations.map(renderAbstractOperation),
    operations.map(renderOperationValidator.bind(null, jsonFileName)),
    renderAbstractRouter(jsonFileName, operations),
    `}`
  ];
}

function renderAbstractOperation(operation) {
  let paramList = renderParamsList(operation.parameters, true);
  let lineToPrint = `abstract protected function ${operation.operationId}(${paramList}): Response;`;
  if (lineToPrint.length > 96) {
    let newParamList = paramList.replace(/, /g, `,
        `);
    lineToPrint = `abstract protected function ${operation.operationId}(
        ${newParamList}
    ): Response;`;
  }
  let paramListDoc = paramList.split(', ').map(param => {
    if (param.includes('?string')) {
      param = param.replace('?string', 'string|null');
    }

    if (param.includes('?int')) {
      param = param.replace('?int', 'int|null');
    }

    if (param.includes('?bool')) {
      param = param.replace('?bool', 'bool|null');
    }

    return '     * @param ' + param;
  }).join('\n');
  paramListDoc += '\n     * @return Response';

  return `
    /**
     * Endpoint: /${operation.path}
     * Method: ${operation.method.toUpperCase()}
     *
${paramListDoc}
     */
    ${lineToPrint}`;
}

function renderOperationValidator(jsonFileName, operation) {
  const { operationId, parameters, path } = operation;
  const hasPathParams = path.includes('{');
  let paramList = renderParamsList(parameters);
  let newParamList = paramList.replace(/, /g, `,\n                `);
  let functionName = util.capitalise(operationId);
  const pathPartsStr = pathParts(operation.path);
  const pathParams = hasPathParams
    ? `
        $pathParts = explode(\'/\', $request->getPath());
        $pathParams = $this->getPathParams(
            [${pathPartsStr}],
            $pathParts
        );`
    : '';

  return [
    `
    private function validateAndCall${functionName}(Request $request): Response
    {${pathParams}`,
    parameters.find(p => p.in === 'formData')
      ? `        $formDataParams = $request->postParams;`
      : [],
    parameters.find(p => p.in === 'query')
      ? `        $queryParams = $request->getParams;`
      : [],
    parameters.find(p => p.in === 'body')
      ? `        $bodyParams = $request->getBodyPayload();`
      : [],
    parameters.find(p => p.in === 'cookie')
      ? `        $cookieParams = $request->getCookie();`
      : [],

    `        $errors = [];\n`,

    parameters.map(p => renderParamValidator(p)),
    renderResponse(operationId, newParamList, jsonFileName)
  ];
}

function renderResponse(operationId, newParamList, jsonFileName) {
  return     `        if ($errors) {
            return Response::createBadRequestResponse(
                [
                    'success' => false,
                    'errorMessage' => 'INVALID_PARAMETERS',
                    'errorInfo' => $errors
                ]
            );
        }

        try {
            return $this->${operationId}(
                ${newParamList}
            );
        } catch (Throwable $t) {
            Core::getDefaultLogger()->logError(
                'Unexpected error in ${jsonFileName}ControllerAbstract - Method: ${operationId}()' .
                ' Error: ' . $t->getMessage() .
                ' Trace: ' . $t->getTraceAsString()
            );
            return Response::createErrorResponse();
        }
    }`;
}

function renderParamsList(parameters, includeType = false) {
  let buffer = [];

  parameters.map(param => {
    if (param.in !== 'undefined' && param.in === 'body') {
      let p = param.schema.properties;
      const requiredParams = param.schema.required ? param.schema.required : [];
      for (let key in p) {
        if (includeType) {
          let current = {
            type: p[key].type,
            required: requiredParams.indexOf(key) !== -1
          };
          buffer.push(util.renderParamType(current) + `$${util.snakeToCamel(key)}`);
        } else {
          buffer.push(`$${util.snakeToCamel(key)}`);
        }
      }
    } else {
      if (includeType) {
        buffer.push(util.renderParamType(param) + `$${util.snakeToCamel(param.name)}`);
      } else {
        buffer.push(`$${util.snakeToCamel(param.name)}`);
      }
    }
  });

  let paramsString = buffer.join(', ');

  // Return Request $request as the last parameter in all methods
  let leadComma = paramsString.length > 0 ? ', ' : '';
  return paramsString + leadComma + (includeType ? 'Request $request' : '$request');
}

function renderParamValidator(param) {
  const buffer = [];
  const varName = `$${param.in}Params['${param.name}']`;

  if (param.in === 'body') {
    buffer.push(renderBodyParameterIf(param, param.name));
  } else if (param.required !== 'undefined' && param.required) {
      buffer.push(renderFormDataRequiredParameterIf(varName, param.type, param.name, param.format));
  } else {
    buffer.push(renderNotRequiredParameterIf(varName, param.type, param.name));
  }

  return buffer;
}

function renderBodyParameterIf(param, name) {
  let buffer = [];
  if (param.required !== 'undefined' && param.required) {
    buffer.push(`        if (!isset($bodyParams)) {
            $errors[] = [
                'field' => '${name}',
                'message' => 'required'
            ];
        }\n\n`);
  }

  if (param.schema && param.schema.properties) {
    const requiredParams = param.schema.required ? param.schema.required : [];
    let p = param.schema.properties;
    for (let key in p) {
      if (p.hasOwnProperty(key)) {
        let isRequired = requiredParams.indexOf(key) !== -1;
        if (isRequired) {
          buffer.push(`        $${util.snakeToCamel(key)} = null;
        if (!isset($bodyParams['${key}'])) {
            $errors[] = [
                'field' => '${key}',
                'message' => 'required'
            ];
        } else {${renderParameterTypeValidation(`$bodyParams['${key}']`, param.type, key, param.format)}
        }\n\n`);
        } else {
          buffer.push(`        $${util.snakeToCamel(key)} = $bodyParams['${key}'] ?? null;\n`);
        }
      }
    }
  }

  return buffer.join('');
}

function renderFormDataRequiredParameterIf(varName, type, name, format) {
  return `        $${util.snakeToCamel(name)} = null;
        if (!isset(${varName})) {
            $errors[] = [
                'field' => '${name}',
                'message' => 'required'
            ];
        } else {${renderParameterTypeValidation(varName, type, name, format)}
        }\n`;
}

function renderParameterTypeValidation(varName, type, name, format) {
  if (type === 'integer' || type === 'number') {
    return `
            if (!is_numeric(${varName})) {
                $errors[] = [
                    'field' => '${name}',
                    'message' => 'must be an ${type}'
                ];
            } else {
                $${util.snakeToCamel(name)} = intval(${varName});
            }`;
  }

  if (type === 'number' && format === 'float') {
    return `
            if (!is_numeric(${varName})) {
                $errors[] = [
                  'field' => '${name}',
                  'message' => 'must be an ${type}(${format})'
                ];
            } else {
                $${util.snakeToCamel(name)} = floatval(${varName});
            }`;
  }

  if (type === 'number' && format === 'double') {
    return `
            if (!is_numeric(${varName})) {
                $errors[] = [
                    'field' => '${name}',
                    'message' => 'must be an ${type}(${format})'
                ];
            } else {
                $${util.snakeToCamel(name)} = doubleval(${varName});
            }`;
  }

  if (type === 'boolean') {
    return `
            $${util.snakeToCamel(name)} = StringUtil::isTrue(${varName});`;
  }

  return `
            $${util.snakeToCamel(name)} = ${varName} ?? null;`;
}

function renderNotRequiredParameterIf(varName, type, name) {
  if (type === 'integer') {
    return `
        if (isset(${varName}) && strlen(${varName}) > 0) {
            $${util.snakeToCamel(name)} = intval(${varName});
        } else {
            $${util.snakeToCamel(name)} = null;
        }`;
  }

  if (type === 'number') {
    return `
        if (isset(${varName}) && strlen(${varName}) > 0) {
            $${util.snakeToCamel(name)} = floatval(${varName});
        } else {
            $${util.snakeToCamel(name)} = null;
        }`;
  }

  if (type === 'array') {
    return `
        if (empty(${varName})) {
            $${util.snakeToCamel(name)} = [];
        } else {
            $${util.snakeToCamel(name)} = (array) ${varName};
        }`;
  }

  if (type === 'boolean') {
    return `
        $${util.snakeToCamel(name)} = StringUtil::isTrue(${varName});`;
  }

  return `
        $${util.snakeToCamel(name)} = ${varName} ?? null;`;
}

function renderAbstractRouter(jsonFileName, operations) {
  const response = util.htmlControllers.indexOf(jsonFileName) >= 0
    ? `Response::createUnauthorisedRedirectLoginResponse($request->siteLanguage)`
    : 'Response::createUnauthorizedJsonResponse()';

  return [
    `   
    public function route(Request $request): ?Response
    {
        $auth = $this->authenticate($request);
        if ($auth !== true) {
            return ${response};
        }

        $path = $request->getPath();
        $pathParts = explode('/', $path);
        $method = $request->method;`,
    operations.map(renderRouteMatcher),
    `
        return null;
    }`
  ];
}

function renderRouteMatcher(operation) {
  const { method, path, operationId } = operation;
  const pathPartsArr = operation.path.split("/");
  const pathPartsStr = pathParts(operation.path);
  const hasPathParams = path.includes('{');
  let pathTypes = [];
  let pathTypesStr = "";
  let pathVarName;
  let part;

  for (let i=0; i < pathPartsArr.length; i++ ) {
    part = pathPartsArr[i];
    if (part.includes('{')) {
      pathVarName = part.slice(1, -1);
      let pathVarType = operation.parameters.find(pathParam => pathParam.name === pathVarName).type;
      switch (pathVarType) {
          case "integer":
              pathVarType = "int";
              break;
          default:
              pathVarType = "string";
      }
      pathTypes.push(pathVarType);
    } else {
      pathTypes.push("fixed");
    }
  }

  if (pathTypes.length) {
      pathTypesStr = "'" + pathTypes.join("', '") + "'";
  }

  let lineToPrint = `if ($method === '${method.toUpperCase()}' &&
            ${
            hasPathParams ? '$pathParams = ' : ''
            }$this->pathParamsMatcher(
                [${pathPartsStr}],
                $pathParts,
                [${pathTypesStr}]
            )
        ) {`;

  let functionName = util.capitalise(operationId);
  return `
        ${lineToPrint}
            return $this->validateAndCall${functionName}($request);
        }`;
}

function pathParts(path) {
  return path
    .split('/')
    .map(part => "'" + part + "'")
    .join(', ');
}
