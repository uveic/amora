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

document.querySelectorAll('.save-user-account-button').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const userId = document.querySelector('input[name="userId"]');
    const name = document.querySelector('input[name="name"]');
    const email = document.querySelector('input[name="email"]');
    const languageId = document.querySelector('select[name="languageId"]');
    const timezone = document.querySelector('select[name="timezone"]');
    const currentPassword = document.querySelector('input[name="currentPassword"]');
    const newPassword = document.querySelector('input[name="newPassword"]');
    const repeatPassword = document.querySelector('input[name="repeatPassword"]');

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
});

export {feedbackDiv};
