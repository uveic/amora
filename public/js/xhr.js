async function logError(errorMessage = null, endpoint = null, method = null, payload = null) {
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
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify(data)
    }
  );
}

function request(url, stringPayload, method = 'POST', feedbackDivEl = null, successMessage = null) {
  const headers = {
    "Content-Type": "application/json"
  };

  return fetch(
    url,
    {
      method: method,
      headers: headers,
      body: stringPayload
    }
  ).then((response) => {
    if (!response.ok) {
      throw new Error(response.status + ': ' + response.statusText);
    }

    try {
      return response.json();
    } catch (error) {
      throw new Error(error.message);
    }
  }).then((json) => {
    if (!json.success) {
      throw new Error(json.errorMessage ?? 'Something went wrong, please try again.');
    }

    if (successMessage && successMessage) {
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

    logError(error.message, url, method, stringPayload).then();

    return Promise.reject(error);
  }).finally(() => {
    if (feedbackDivEl) {
      setTimeout(() => {feedbackDivEl.classList.add('null')}, 5000);
    }
  });
}

function get(url, feedbackDivEl = null, successMessage = null) {
  return request(url, null, 'GET', feedbackDivEl, successMessage);
}

function post(url, stringPayload, feedbackDivEl = null, successMessage = null) {
  return request(url, stringPayload, 'POST', feedbackDivEl, successMessage);
}

function put(url, stringPayload, feedbackDivEl = null, successMessage = null) {
  return request(url, stringPayload, 'PUT', feedbackDivEl, successMessage);
}

function _delete(url, feedbackDivEl = null, successMessage = null) {
  return request(url, null, 'DELETE', feedbackDivEl, successMessage);
}

export const xhr = {
  get,
  post,
  put,
  delete: _delete
};
