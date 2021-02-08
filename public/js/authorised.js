import {xhr} from './module/xhr.js';
import {global} from './module/localisation.js';

const feedbackDiv = document.querySelector('#feedback');

document.querySelectorAll('a.verified-link').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const userId = el.dataset.userId;

    xhr.post('/api/user/' + userId + '/verification-email', null, feedbackDiv, 'Enviado');
  });
});

const formUserAccount = document.querySelector('#form-user-account');
if (formUserAccount) {
  formUserAccount.addEventListener('submit', e => {
    e.preventDefault();

    const userId = document.querySelector('form#form-user-account input[name="userId"]');
    const name = document.querySelector('form#form-user-account input[name="name"]');
    const email = document.querySelector('form#form-user-account input[name="email"]');
    const languageId = document.querySelector('form#form-user-account select[name="languageId"]');
    const timezone = document.querySelector('form#form-user-account select[name="timezone"]');
    const currentPassword = document.querySelector('form#form-user-account input[name="currentPassword"]');
    const newPassword = document.querySelector('form#form-user-account input[name="newPassword"]');
    const repeatPassword = document.querySelector('form#form-user-account input[name="repeatPassword"]');

    const payload = {
      name: name ? name.value : null,
      email: email ? email.value : null,
      languageId: languageId ? languageId.value : null,
      timezone: timezone ? timezone.value : null,
      currentPassword: currentPassword ? currentPassword.value : null,
      newPassword: newPassword ? newPassword.value : null,
      repeatPassword: repeatPassword ? repeatPassword.value : null
    };

    xhr.put(
      '/api/user/' + userId.value,
      JSON.stringify(payload),
      feedbackDiv,
      global.get('feedbackAccountUpdated')
    );
  });
}

export {feedbackDiv};
