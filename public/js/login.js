import {Request} from './module/Request.js?v=000';
import {Global} from './module/localisation.js?v=000';

document.querySelectorAll('.form-login-workflow-js input').forEach(el => {
  el.addEventListener('input', () => {
    document.querySelector('#login-failure-message').classList.add('null');
  });
});

document.querySelectorAll('form#form-login').forEach(fl => {
  fl.addEventListener('submit', e => {
    e.preventDefault();

    const loginFailureFeedback = document.querySelector('#login-failure-message');
    loginFailureFeedback.classList.add('null')

    const user = fl.querySelector('input[name="user"]');
    const password = fl.querySelector('input[name="password"]');
    const siteLanguage = document.documentElement.lang
      ? document.documentElement.lang.toLowerCase().trim()
      : 'en';

    const data = {
      user: user.value,
      password: password.value,
      languageIsoCode: siteLanguage
    };

    Request.post('/papi/login', JSON.stringify(data))
      .then((response) => window.location = response.redirect)
      .catch((error) => {
        password.value = '';
        loginFailureFeedback.textContent = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
});

document.querySelectorAll('form#form-register').forEach(fr => {
  fr.addEventListener('submit', e => {
    e.preventDefault();

    const loginFailureFeedback = document.querySelector('#login-failure-message');
    loginFailureFeedback.classList.add('null');

    const email = fr.querySelector('input[name="email"]');
    const password = fr.querySelector('input[name="password"]');
    const name = fr.querySelector('input[name="name"]');
    const siteLanguage = document.documentElement.lang
      ? document.documentElement.lang.toLowerCase().trim()
      : 'en';

    if (password.value.length < 10) {
      loginFailureFeedback.textContent = Global.get('feedbackPasswordTooShort');
      loginFailureFeedback.classList.remove('null');
      password.focus();
      return;
    }

    const data = {
      languageIsoCode: siteLanguage,
      email: email.value,
      password: password.value,
      name: name.value,
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    };

    Request.post('/papi/register', JSON.stringify(data))
      .then((response) => window.location = response.redirect)
      .catch((error) => {
        loginFailureFeedback.innerHTML = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
});

document.querySelectorAll('form#form-login-forgot').forEach(flf => {
  flf.addEventListener('submit', e => {
    e.preventDefault();

    const loginFailureFeedback = document.querySelector('#login-failure-message');
    loginFailureFeedback.classList.add('null');

    const email = flf.querySelector('input[name="email"]').value;

    const data = {
      email: email,
    };

    Request.post('/papi/login/forgot', JSON.stringify(data))
      .then(() => {
        const emailFeedback = document.querySelector('span#register-feedback-email');
        if (emailFeedback) {
          emailFeedback.textContent = email;
        }
        document.querySelector('div#register-form').classList.add('null');
        document.querySelector('div#register-back-login').classList.remove('null');
      })
      .catch((error) => {
        loginFailureFeedback.textContent = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
});

document.querySelectorAll('form#form-password-reset').forEach(fpr => {
  fpr.addEventListener('submit', e => {
    e.preventDefault();

    const loginFailureFeedback = document.querySelector('#login-failure-message');
    loginFailureFeedback.classList.add('null');

    const userId = fpr.querySelector('input[name="userId"]').value;
    const password = fpr.querySelector('input[name="password"]');
    const passwordConfirmation = fpr.querySelector('input[name="passwordConfirmation"]');
    const validationHash = fpr.querySelector('input[name="validationHash"]').value;
    const postUrl = fpr.querySelector('input[name="postUrl"]').value;
    const verificationIdentifier = fpr.querySelector('input[name="verificationIdentifier"]').value;

    if (password.value.length < 10) {
      loginFailureFeedback.textContent = Global.get('feedbackPasswordTooShort');
      loginFailureFeedback.classList.remove('null');
      password.focus();
      return;
    }

    if (passwordConfirmation.value !== password.value) {
      loginFailureFeedback.textContent = Global.get('feedbackPasswordsDoNotMatch');
      loginFailureFeedback.classList.remove('null');
      password.focus();
      return;
    }

    const siteLanguage = document.documentElement.lang
      ? document.documentElement.lang.toLowerCase().trim()
      : 'en';

    const data = {
      userId: Number.parseInt(userId),
      password: password.value,
      passwordConfirmation: passwordConfirmation.value,
      validationHash: validationHash,
      verificationIdentifier: verificationIdentifier,
      languageIsoCode: siteLanguage
    };

    Request.post(postUrl, JSON.stringify(data))
      .then(() => {
        document.querySelector('div#password-reset-form').classList.add('null');
        document.querySelector('div#password-reset-success').classList.remove('null');
      }).catch((error) => {
      loginFailureFeedback.innerHTML = error.message;
      loginFailureFeedback.classList.remove('null');
    });
  });
});

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

    const loginFailureFeedback = document.querySelector('#login-failure-message');
    loginFailureFeedback.classList.add('null');

    const email = f.querySelector('input[name="email"]');
    const siteLanguage = document.documentElement.lang
      ? document.documentElement.lang.toLowerCase().trim()
      : 'en';

    const data = {
      email: email.value,
      languageIsoCode: siteLanguage,
    };

    Request.post('/papi/invite-request', JSON.stringify(data))
      .then(() => {
        document.querySelector('span#register-feedback-email').textContent = email.value;
        document.querySelector('div.div-request-form').classList.add('null');
        document.querySelector('div#request-form-feedback').classList.remove('null');
      })
      .catch((error) => {
        loginFailureFeedback.textContent = error.message;
        loginFailureFeedback.classList.remove('null');
      });
  });
});
