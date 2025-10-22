const spec = require('swagger-tools').specs.v2; // Using the latest Swagger 2.x specification
const fs = require('fs');
const path = require('path');
const util = require('./lib/util');

const controllersData = [
  {
    prefix: 'Core',
    path: __dirname + '/../../../Core/Router/Controller/',
  },
  {
    prefix: 'App',
    path: __dirname + '/../../../App/Router/Controller/',
  },
];
const phpControllerTemplate = require('./class.php');
const phpResponsesTemplate = require('./responses.php');

process.on('unhandledRejection', error => {
  console.error(error);
});

controllersData.forEach(cd => {
  const controllerFolderPath = cd.path;
  const classPrefix = cd.prefix;

  fs.readdir(controllerFolderPath, (err, list) => {
    if (err) {
      throw err;
    }

    list.forEach(file => {
      if (path.extname(file) === '.json') {
        let jsonFileName = path.basename(file, '.json');
        parseSwaggerFile(controllerFolderPath + file,
          function (fileOperations) {
            generateControllerCode(
              jsonFileName,
              fileOperations,
              getAllResponseClassNames(jsonFileName, fileOperations),
              controllerFolderPath,
              classPrefix,
            );
            generateResponsesCode(jsonFileName, fileOperations, controllerFolderPath, classPrefix);
          }
        );
      }
    });
  });
});

function saveAndLog(file, str) {
  fs.writeFileSync(file, str);
  console.log('Generated', file);
}

function parseSwaggerFile(filename, callback) {
  const swaggerObject = JSON.parse(fs.readFileSync(filename, 'utf-8'));

  spec.validate(swaggerObject, (err, result) => {
    if (err) {
      throw err;
    }

    if (typeof result !== 'undefined') {
      if (result.errors.length > 0) {
        console.log('The Swagger document is invalid...');

        console.log('');

        console.log('Errors');
        console.log('------');

        result.errors.forEach(err => {
          console.log('#/' + err.path.join('/') + ': ' + err.message);
        });

        console.log('');
      }

      if (result.warnings.length > 0) {
        console.log('Warnings');
        console.log('--------');

        result.warnings.forEach(warn => {
          console.log('#/' + warn.path.join('/') + ': ' + warn.message);
        });
      }

      if (result.errors.length > 0) {
        console.log('Aborting...');
        process.exit(1);
      }
    }

    spec.resolve(swaggerObject, (err, result) => {
      const globalParams = [];
      for (const securityObj of result.security) {
        for (const key of Object.keys(securityObj)) {
          const param = result.securityDefinitions[key];
          if (param
            && param.type === 'apiKey'
            && param.in === 'header'
            && param.name.substring(0,7) === 'cookie-')
          {
            globalParams.push(
              {
                name: param.name.substring(7),
                required: true,
                in: 'cookie',
                type: 'string'
              }
            );
          }
        }
      }

      const operations = [];

      for (const [path, methods] of Object.entries(result.paths)) {
        for (const [method, operation] of Object.entries(methods)) {
          operation.parameters = operation.parameters || [];
          operation.parameters = globalParams.concat(operation.parameters);
          operation.path = path.substring(1);
          operation.method = method;
          operations.push(operation);
        }
      }

      callback(operations);
    });
  });
}

function generateControllerCode(jsonFileName, operations, allResponseClassNames, controllerFolderPath, classPrefix) {
  const phpCodeString = phpControllerTemplate(jsonFileName, operations, allResponseClassNames, classPrefix);
  saveAndLog(
    controllerFolderPath + jsonFileName + 'ControllerAbstract.php',
    phpCodeString
  );
}

function generateResponsesCode(jsonFileName, operations, controllerFolderPath, classPrefix) {
  if (util.htmlControllers.indexOf(jsonFileName) >= 0) {
    return;
  }

  operations.map(operation => {
    const {operationId, responses} = operation;

    for (let responseCode in responses) {
      if (responses.hasOwnProperty(responseCode)) {
        let response = responses[responseCode];
        let { type, httpStatus } = util.httpResponseStatus(responseCode);
        let className = util.generateResponseClassName(jsonFileName, operationId, type);
        let phpCodeString = phpResponsesTemplate(className, operationId, httpStatus, response.schema, classPrefix);

        saveAndLog(
          controllerFolderPath + 'response/' + className + '.php',
          phpCodeString
        );
      } else {
        console.log('Response code not found for operation ID: ' + operationId + '. Aborting...');
        process.exit(1);
      }
    }
  });
}

function getAllResponseClassNames(jsonFileName, operations) {
  if (util.htmlControllers.indexOf(jsonFileName) >= 0) {
    return [];
  }

  let output = [];

  operations.map(operation => {
    const {operationId, responses} = operation;

    for (let responseCode in responses) {
      if (responses.hasOwnProperty(responseCode)) {
        let status = util.httpResponseStatus(responseCode);
        let className = util.generateResponseClassName(jsonFileName, operationId, status.type);
        output.push(className);
      }
    }
  });

  return output;
}
