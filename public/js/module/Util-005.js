import {global} from './localisation-002.js';

class UtilClass {
  getUpdatedAtTime() {
    const now = new Date();
    const prefix = ' ' + global.get('globalAt') + ' ';

    return prefix + now.getHours().toString().padStart(2, '0') +
      ':' + now.getMinutes().toString().padStart(2, '0');
  }

  cleanString(text) {
    return text
      .trim()
      .toLowerCase()
      .replace(/á/g, 'a')
      .replace(/é/g, 'e')
      .replace(/í/g, 'i')
      .replace(/ó/g, 'o')
      .replace(/ú/g, 'u')
      .replace(/ñ/g, 'n')
      .replace(/\s{2,}/g,' ')
      .replace(/\s/g,'-')
      .replace(/[^A-Za-z0-9\-]/g,'-')
      .replace(/-+/g, '-')
      .replace(/-$/g, '');
  }

  logError(error, errorMessage = null) {
    const feedbackDiv = document.querySelector('#feedback');

    if (!feedbackDiv) return;

    feedbackDiv.textContent = errorMessage ?? (error.message ?? global.get('genericError'));
    feedbackDiv.classList.remove('feedback-success');
    feedbackDiv.classList.add('feedback-error');
    feedbackDiv.classList.remove('null');
    setTimeout(() => {feedbackDiv.classList.add('null')}, 15000);
  }

  notifyUser(message) {
    const feedbackDiv = document.querySelector('#feedback');

    if (!feedbackDiv) return;

    feedbackDiv.textContent = message;
    feedbackDiv.classList.add('feedback-success');
    feedbackDiv.classList.remove('feedback-error');
    feedbackDiv.classList.remove('null');
    setTimeout(() => {feedbackDiv.classList.add('null')}, 5000);
  }

  createMediumEditor(containerClassName) {
    new MediumEditor(
      '.' + containerClassName,
      {
        autoLink: true,
        targetBlank: true,
        anchor: {
          linkValidation: true,
          placeholderText: global.get('editorLinkPlaceholder'),
          targetCheckbox: true,
          targetCheckboxText: global.get('editorLinkOpenBlank'),
        },
        placeholder: {
          text: global.get('editorParagraphPlaceholder'),
          hideOnClick: true
        },
        paste: {
          cleanPastedHTML: true,
          cleanAttrs: ['style', 'dir', 'class', 'title'],
          cleanTags: ['label', 'meta', 'input']
        },
      }
    );
  }
}

export const Util = new UtilClass();
