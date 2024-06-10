import {Request} from './module/Request.js?v=000';
import {Global} from './module/localisation.js?v=000';

document.querySelectorAll('a.verified-link').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const userId = el.dataset.userId;

    Request.post('/api/user/' + userId + '/verification-email', null, Global.get('globalSent'))
      .then();
  });
});

document.querySelectorAll('form#form-user-account-update').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const userId = document.querySelector('input[name="userId"]');
    const name = document.querySelector('input[name="name"]');
    const email = document.querySelector('input[name="email"]');
    const languageIsoCode = document.querySelector('select[name="languageIsoCode"]');
    const timezone = document.querySelector('select[name="timezone"]');
    const currentPassword = document.querySelector('input[name="currentPassword"]');
    const newPassword = document.querySelector('input[name="newPassword"]');
    const repeatPassword = document.querySelector('input[name="repeatPassword"]');

    const payload = {
      name: name ? name.value : null,
      email: email ? email.value : null,
      languageIsoCode: languageIsoCode ? languageIsoCode.value : null,
      timezone: timezone ? timezone.value : null,
      currentPassword: currentPassword ? currentPassword.value : null,
      newPassword: newPassword ? newPassword.value : null,
      repeatPassword: repeatPassword ? repeatPassword.value : null
    };

    Request.put(
      '/api/user/' + userId.value,
      JSON.stringify(payload),
      Global.get('feedbackAccountUpdated')
    ).then();
  });
});

document.querySelectorAll('.nav-dropdown-toggle-label').forEach(el => {
  el.addEventListener('click', e => {
    document.querySelectorAll('.nav-dropdown-toggle').forEach(dr => {
      if (dr.id !== e.target.htmlFor) {
        document.querySelector('#' + dr.id).checked = false;
      }
    });
  });
});

document.querySelectorAll('.modal-close-button').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();
    el.parentElement.parentElement.classList.add('null');
  });
});
