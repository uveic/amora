exports.renderParamType = function(param) {
  let phpParamType;

  switch (param.type) {
    case 'integer':
      phpParamType = 'int';
      break;
    case 'number':
      phpParamType = 'float';
      break;
    case 'boolean':
      phpParamType = 'bool';
      break;
    case 'object':
      phpParamType = '';
      break;
    default:
      phpParamType = param.type;
      break;
  }

  if (!param.required && phpParamType) {
    phpParamType = '?' + phpParamType;
  }

  return phpParamType ? phpParamType + ' ' : phpParamType;
}

exports.flatten = function(arg) {
  let arr = arg;
  while (arr.find(el => Array.isArray(el)))
    arr = Array.prototype.concat(...arr);

  return arr;
}

const capitalise = function(p) {
  return p.charAt(0).toUpperCase() + p.slice(1);
}
exports.capitalise = capitalise;

exports.snakeToCamel = function(s) {
  return s.replace(/(_\w)/g, function(m){return m[1].toUpperCase();});
}

exports.httpResponseStatus = function(responseCode) {
  switch (responseCode) {
    case '400':
      return {
        type: 'FailureResponse',
        httpStatus: 'HTTP_400_BAD_REQUEST'
      };
    case '500':
      return {
        type: 'ErrorResponse',
        httpStatus: 'HTTP_500_INTERNAL_ERROR'
      };
    case '403':
      return {
        type: 'ForbiddenResponse',
        httpStatus: 'HTTP_403_FORBIDDEN'
      };
    case '404':
      return {
        type: 'NotFoundResponse',
        httpStatus: 'HTTP_404_NOT_FOUND'
      };
    default:
      return {
        type: 'SuccessResponse',
        httpStatus: 'HTTP_200_OK'
      };
  }
}

exports.generateResponseClassName = function(jsonFileName, operationId, responseTypeName) {
  return jsonFileName + 'Controller' + capitalise(operationId + responseTypeName);
}
