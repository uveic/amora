const util = require('./lib/util');

module.exports = function generate(className, operationId, httpStatus, responseSchema, classPrefix) {
  return util.flatten(renderMain(className, operationId, httpStatus, responseSchema, classPrefix)).join('\n');
};

function renderMain(className, operationId, httpStatus, responseSchema, classPrefix) {
  return [
    `<?php
namespace Amora\\${classPrefix}\\Router\\Controller\\Response;

use Amora\\Core\\Entity\\Response;
use Amora\\Core\\Entity\\HttpStatusCode;
`,
    renderResponseClass(className, operationId, httpStatus, responseSchema),
    ``
  ];
}

function renderResponseClass(responseClassName, operationId, httpStatus, schema) {
  // default params will be used if it isn't overridden
  let params = [];
  const optionalParams = [];
  const requiredParams = [];
  const presetParams = [];

  if (!schema) {
    console.log('Schema is empty for ' + operationId + '. No file generated. Skipping...');
    return [];
  }

  if (schema.type === 'object') {
    const { required, properties } = schema;

    Object.entries(properties).forEach(propertyArr => {
      const [propertyName, param] = propertyArr;
      param.name = propertyName;
      param.required = required
        ? required.indexOf(propertyName) !== -1
        : false;

      if (param.enum && param.enum.length === 1) {
        param.value = param.enum[0];
        presetParams.push(param);
      } else if (param.required) {
        requiredParams.push(param);
      } else {
        optionalParams.push(param);
      }
    });

    // we want optional params to be listed after required
    // but with the original order maintained within the lists
    params = requiredParams.concat(optionalParams);
  } else if (schema.type === 'array' || schema.type === 'string') {
    params.push({
      name: 'responseData',
      type: 'object',
      required: true
    });
  }

  const paramsList = params
    .map(p => {
      return `${util.renderParamType(p)}$${util.snakeToCamel(p.name)}${p.required ? '' : ' = null'}`;
    })
    .join(', ');
  const isResponseDataOnly =
    params.length === 1 && params[0].name === 'responseData';

  let lineToPrint = `    public function __construct(${paramsList})
    {`;
  if (lineToPrint.length > 96) {
    let newParamsList = paramsList.replace(/, /g, `,\n        `);
    lineToPrint = `    public function __construct(
        ${newParamsList}
    ) {`;
  }

  return [
    `class ${responseClassName} extends Response
{`,
    params
      .filter(p => p.enum)
      .map(p => p.enum.map(e => {
        let eU = null;
        if (typeof(e) == typeof(true)) {
            return [];
        } else {
            eU = e.toUpperCase();
            return [`    const ${eU} = "${e}";`];
        }
      })),
    `${lineToPrint}`,
    !isResponseDataOnly
      ? [
          `        // Required parameters`,
          `        $responseData = [`,
          presetParams.map(function(param) {
            if (param.type === 'string') {
              return `            '${param.name}' => '${param.value}',`;
            } else {
              return `            '${param.name}' => ${param.value},`;
            }
          }),
          requiredParams.map(
            param => `            '${param.name}' => $${util.snakeToCamel(param.name)},`
          ),
          `        ];
`
        ]
      : [],
    optionalParams.map(param => [
      `        $responseData['${param.name}'] = is_null($${util.snakeToCamel(param.name)})
            ? null
            : $${util.snakeToCamel(param.name)};\n`
    ]),
    `        [$output, $contentType] = self::getResponseType($responseData);`,
    `        parent::__construct($output, $contentType, HttpStatusCode::${httpStatus});`,
    `    }`,
    `}`
  ];
}
