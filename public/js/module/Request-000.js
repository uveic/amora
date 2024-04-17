import {Global} from "./localisation-000.js";
import {Util} from "./Util-000.js";

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
    stringPayload,
    method = 'POST',
    headers,
    successMessage = null
  ) {
    return fetch(
      url,
      {
        method: method,
        headers: headers,
        body: stringPayload
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
      this.logError(error.message, url, method, stringPayload).then();

      return Promise.reject(error);
    });
  }

  get(url, successMessage = null) {
    const headers = {'Content-Type': 'application/json'};
    return this.request(url, null, 'GET', headers, successMessage);
  }

  post(url, stringPayload, successMessage = null) {
    const headers = {'Content-Type': 'application/json'};
    return this.request(url, stringPayload, 'POST', headers, successMessage);
  }

  put(url, stringPayload, successMessage = null) {
    const headers = {'Content-Type': 'application/json'};
    return this.request(url, stringPayload, 'PUT', headers, successMessage);
  }

  delete(url, stringPayload = null, successMessage = null) {
    const headers = {'Content-Type': 'application/json'};
    return this.request(url, stringPayload, 'DELETE', headers, successMessage);
  }

  postImage(url, formData, successMessage = null) {
    const headers = {};
    return this.request(url, formData, 'POST', headers, successMessage);
  }
}

export const Request = new RequestClass();
