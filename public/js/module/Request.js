import {Global} from "./localisation.js?v=000";
import {Util} from "./Util.js?v=000";

class RequestClass {
  request(
    url,
    stringPayload = null,
    method = 'POST',
    successMessage = null,
    logError = false,
    timeoutMilliseconds = null,
    headers = {'Content-Type': 'application/json'},
  ) {
    const options = {
      method: method,
      headers: headers,
      body: stringPayload ?? null,
    };

    if (timeoutMilliseconds) {
      options.signal = AbortSignal.timeout(timeoutMilliseconds);
    }

    return fetch(
      url,
      options
    ).then((response) => {
      if (!response.ok) {
        throw new Error(
          Global.get('genericErrorGetInTouch') + response.status + ': ' + response.statusText
        );
      }

      try {
        return response.json();
      } catch (error) {
        throw new Error(error.message);
      }
    }).then((json) => {
      if (!json.success) {
        throw new Error(json.errorMessage ?? Global.get('genericError'));
      }

      if (successMessage) {
        Util.notifyUser(successMessage);
      }

      return Promise.resolve(json);
    }).catch(error => {
      Util.notifyError(error);

      if (logError) {
        this.logMessage(error.message, url, method, stringPayload, true).then();
      }

      return Promise.reject(error);
    });
  }

  async logMessage(
    errorMessage = null,
    endpoint = null,
    method = null,
    payload = null,
    isError = true,
  ) {
    if (typeof payload !== 'string') {
      payload = 'Not a string';
    }

    const data = {
      isError: isError,
      endpoint: endpoint,
      method: method,
      payload: payload,
      errorMessage: errorMessage,
      userAgent: navigator.userAgent,
      pageUrl: window.location.href
    };

    return fetch(
      '/papi/log',
      {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
      }
    );
  }

  get(url, successMessage = null, logError = false, timeoutMilliseconds = null) {
    return this.request(url, null, 'GET', successMessage, logError, timeoutMilliseconds = null);
  }

  post(url, stringPayload = null, successMessage = null, logError = false, timeoutMilliseconds = null) {
    return this.request(url, stringPayload, 'POST', successMessage, logError, timeoutMilliseconds);
  }

  put(url, stringPayload = null, successMessage = null, logError = false, timeoutMilliseconds = null) {
    return this.request(url, stringPayload, 'PUT', successMessage, logError, timeoutMilliseconds);
  }

  delete(url, stringPayload = null, successMessage = null, logError = false, timeoutMilliseconds = null) {
    return this.request(url, stringPayload, 'DELETE', successMessage, logError, timeoutMilliseconds);
  }
}

export const Request = new RequestClass();
