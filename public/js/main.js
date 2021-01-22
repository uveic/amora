import {xhr} from './xhr.js';

const formLogin = document.querySelector('form#form-login');
const loginFailureFeedback = document.querySelector('#login-failure-message');

const mainImageDivAll = document.querySelectorAll('.home-section-right');
const rotateMainImage = function () {
  mainImageDivAll.forEach(el => el.classList.toggle('null'));
  setTimeout(rotateMainImage, 5000);
};
rotateMainImage();

if (loginFailureFeedback) {
  document.querySelectorAll('input').forEach(el => {
    el.addEventListener('input', () => {
      loginFailureFeedback.classList.add('null');
    });
  });
}

if (formLogin) {
  formLogin.addEventListener('submit', e => {
    e.preventDefault();
    loginFailureFeedback.classList.add('null');

    const user = document.querySelector('form#form-login input[name="user"]');
    const password = document.querySelector('form#form-login input[name="password"]');

    const data = {
      'user': user.value,
      'password': password.value
    };

    xhr.post('/papi/login', JSON.stringify(data))
      .then(() => {
        window.location = '/backoffice/dashboard';
      }).catch((error) => {
        password.value = '';
        loginFailureFeedback.textContent = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
}

const formRegister = document.querySelector('form#form-register');
if (formRegister) {
  formRegister.addEventListener('submit', e => {
    e.preventDefault();
    loginFailureFeedback.classList.add('null');

    const email = document.querySelector('form#form-register input[name="email"]');
    const password = document.querySelector('form#form-register input[name="password"]');
    const name = document.querySelector('form#form-register input[name="name"]');

    if (password.value.length < 10) {
      loginFailureFeedback.textContent = 'Lonxitude mínima: 10 caracteres. Corríxeo e volve a intentalo.';
      loginFailureFeedback.classList.remove('null');
      password.focus();
      return;
    }

    const d = new Date();
    const data = {
      'email': email.value,
      'password': password.value,
      'name': name.value,
      'timezoneOffsetMinutes': d.getTimezoneOffset() * -1
    };

    xhr.post('/papi/register', JSON.stringify(data))
      .then(() => {
        window.location = '/backoffice/dashboard';
      }).catch((error) => {
        loginFailureFeedback.innerHTML = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
}

const formLoginForgot = document.querySelector('form#form-login-forgot');
if (formLoginForgot) {
  formLoginForgot.addEventListener('submit', e => {
    e.preventDefault();
    loginFailureFeedback.classList.add('null');

    const email = document.querySelector('form#form-login-forgot input[name="email"]');

    const data = {
      'email': email.value
    };

    xhr.post('/papi/login/forgot', JSON.stringify(data))
      .then(() => {
        document.querySelector('span#register-feedback-email').textContent = email.value;
        document.querySelector('div#register-form').classList.add('null');
        document.querySelector('div#register-back-login').classList.remove('null');
      }).catch((error) => {
        loginFailureFeedback.textContent = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
}

const formPasswordReset = document.querySelector('form#form-password-reset');
if (formPasswordReset) {
  formPasswordReset.addEventListener('submit', e => {
    e.preventDefault();
    loginFailureFeedback.classList.add('null');

    const userId = document.querySelector('form#form-password-reset input[name="userId"]');
    const password = document.querySelector('form#form-password-reset input[name="password"]');
    const passwordConfirmation = document.querySelector('form#form-password-reset input[name="passwordConfirmation"]');
    const verificationHash = document.querySelector('form#form-password-reset input[name="verificationHash"]');

    if (password.value.length < 10) {
      loginFailureFeedback.textContent = 'Lonxitude mínima: 10 caracteres. Corríxeo e volve a intentalo.';
      loginFailureFeedback.classList.remove('null');
      password.focus();
      return;
    }

    if (passwordConfirmation.value !== password.value) {
      loginFailureFeedback.textContent = 'Os contrasinais non coinciden. Corríxeo e volve a intentalo.';
      loginFailureFeedback.classList.remove('null');
      password.focus();
      return;
    }

    const data = {
      'userId': Number.parseInt(userId.value),
      'password': password.value,
      'passwordConfirmation': passwordConfirmation.value,
      'verificationHash': verificationHash.value
    };

    xhr.post('/papi/login/password-reset', JSON.stringify(data))
      .then(() => {
        document.querySelector('div#password-reset-form').classList.add('null');
        document.querySelector('div#password-reset-success').classList.remove('null');
      }).catch((error) => {
        loginFailureFeedback.innerHTML = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
}

document.querySelectorAll('a.language-picker').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const language = el.dataset.languageCode ?? null;
    if (language) {
      let currentPath = window.location.pathname;
      if (currentPath === '/') {
        currentPath = '';
      }

      const newPath = '/' + language.toLowerCase().trim() +
        currentPath.replace(/^(\/es|\/en|\/gl)/gi, '');
      if (newPath !== currentPath) {
        window.location.href = newPath;
      }
    }
  });
});

document.querySelectorAll('form#form-invite-request').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();
    loginFailureFeedback.classList.add('null');

    const email = document.querySelector('form#form-invite-request input[name="email"]');
    const lg = document.querySelector('form#form-invite-request input[name="languageIsoCode"]');

    const data = {
      'email': email.value,
      'languageIsoCode': lg ? lg.value : null
    };

    xhr.post('/papi/invite-request', JSON.stringify(data))
      .then(() => {
        document.querySelector('span#register-feedback-email').textContent = email.value;
        document.querySelector('div.div-request-form').classList.add('null');
        document.querySelector('div#request-form-feedback').classList.remove('null');
      }).catch((error) => {
        loginFailureFeedback.textContent = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
});
