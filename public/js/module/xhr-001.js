import {global} from "./localisation-002.js";

async function logError(errorMessage = null, endpoint = null, method = null, payload = null) {
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

function request(
  url,
  stringPayload,
  method = 'POST',
  headers,
  feedbackDivEl = null,
  successMessage = null
) {
  let errorResponse = false;

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
        global.get('genericErrorGetInTouch') + response.status + ': ' + response.statusText
      );
    }

    try {
      return response.json();
    } catch (error) {
      throw new Error(error.message);
    }
  }).then((json) => {
    if (!json.success) {
      throw new Error(json.errorMessage ?? global.get('genericError'));
    }

    if (successMessage && feedbackDivEl) {
      feedbackDivEl.classList.remove('null');
      feedbackDivEl.textContent = successMessage;
      feedbackDivEl.classList.remove('feedback-error');
      feedbackDivEl.classList.add('feedback-success');
    }

    return Promise.resolve(json);
  }).catch(error => {
    if (feedbackDivEl) {
      feedbackDivEl.classList.remove('null');
      feedbackDivEl.classList.remove('feedback-success');
      feedbackDivEl.classList.add('feedback-error');
      feedbackDivEl.textContent = error.message;
    }

    errorResponse = true;
    logError(error.message, url, method, stringPayload).then();

    return Promise.reject(error);
  }).finally(() => {
    if (feedbackDivEl) {
      const displayTime = errorResponse ? 15000 : 5000;
      setTimeout(() => {feedbackDivEl.classList.add('null')}, displayTime);
    }
  });
}

function get(url, feedbackDivEl = null, successMessage = null) {
  const headers = {'Content-Type': 'application/json'};
  return request(url, null, 'GET', headers, feedbackDivEl, successMessage);
}

function post(url, stringPayload, feedbackDivEl = null, successMessage = null) {
  const headers = {'Content-Type': 'application/json'};
  return request(url, stringPayload, 'POST', headers, feedbackDivEl, successMessage);
}

function put(url, stringPayload, feedbackDivEl = null, successMessage = null) {
  const headers = {'Content-Type': 'application/json'};
  return request(url, stringPayload, 'PUT', headers, feedbackDivEl, successMessage);
}

function _delete(url, stringPayload = null, feedbackDivEl = null, successMessage = null) {
  const headers = {'Content-Type': 'application/json'};
  return request(url, stringPayload, 'DELETE', headers, feedbackDivEl, successMessage);
}

function postImage(url, formData, feedbackDivEl = null, successMessage = null) {
  const headers = {};
  return request(url, formData, 'POST', headers, feedbackDivEl, successMessage);
}

export const xhr = {
  get,
  post,
  postImage,
  put,
  delete: _delete
};
