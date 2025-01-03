import {Global} from "./localisation.js?v=000";
import {Util} from "./Util.js?v=000";

class RequestClass {
  async logError(errorMessage = null, endpoint = null, method = null, payload = null) {
    if (payload && typeof payload !== 'string') {
      payload = 'Not a string';
    }

    const data = {
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

  request(
    url,
    stringPayload = null,
    method = 'POST',
    successMessage = null,
    logError = false,
    headers = {'Content-Type': 'application/json'},
  ) {
    return fetch(
      url,
      {
        method: method,
        headers: headers,
        body: stringPayload ?? null,
      }
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
        this.logError(error.message, url, method, stringPayload).then();
      }

      return Promise.reject(error);
    });
  }

  get(url, successMessage = null, logError = false) {
    return this.request(url, null, 'GET', successMessage, logError);
  }

  post(url, stringPayload = null, successMessage = null, logError = false) {
    return this.request(url, stringPayload, 'POST', successMessage, logError);
  }

  put(url, stringPayload = null, successMessage = null, logError = false) {
    return this.request(url, stringPayload, 'PUT', successMessage, logError);
  }

  delete(url, stringPayload = null, successMessage = null, logError = false) {
    return this.request(url, stringPayload, 'DELETE', successMessage, logError);
  }
}

export const Request = new RequestClass();
