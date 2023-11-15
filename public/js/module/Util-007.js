import {global} from './localisation-003.js';

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

  logError(errorObj = null, errorMessage = null) {
    const feedbackDiv = document.querySelector('#feedback');

    if (!feedbackDiv) return;

    feedbackDiv.textContent = errorMessage ?? (errorObj ? errorObj.message : global.get('genericError'));
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

  getYoutubeVideoIdFromUrl(url) {
    const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
    const match = url.match(regExp);
    return match && match[7].length === 11 ? match[7] : false;
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

  getAndCleanHtmlFromElement(element, addParagraph = false) {
    const cleanHtml = (html) => {
      if (!html) {
        return '';
      }

      html = html.trim().replace(/^\s+|\s+$/gm, '');

      while (html.length && html.slice(-4) === '<br>') {
        html = html.slice(0, -4).trim();
      }

      return html;
    };

    element.childNodes.forEach(currentNode => {
      if (currentNode.nodeName === '#text' && !currentNode.textContent.trim().length) {
        return;
      }

      if (currentNode.nodeName === 'DIV') {
        const newParagraph = document.createElement('p');
        newParagraph.innerHTML = currentNode.innerHTML;
        element.insertBefore(newParagraph, currentNode);
        element.removeChild(currentNode);
        currentNode = newParagraph;
      }

      if (addParagraph) {
        if (currentNode.nodeName === '#text') {
          if (currentNode.textContent.trim().length) {
            const newParagraph = document.createElement('p');
            newParagraph.textContent = currentNode.textContent;
            element.insertBefore(newParagraph, currentNode);
          }

          element.removeChild(currentNode);
        }
      }

      const html = currentNode.innerHTML ?? currentNode.textContent;
      const currentHtml = cleanHtml(html);

      if (currentNode.nodeName === 'BR') {
        currentNode.parentNode.removeChild(currentNode);
      } else if (!currentHtml.length) {
        element.removeChild(currentNode);
      }
    });

    return element.innerHTML.trim();
  }

  createLoadingAnimation() {
    const loadingAnimation = new Image();
    loadingAnimation.src = '/img/loading.gif';
    loadingAnimation.alt = global.get('globalLoading');
    loadingAnimation.className = 'img-svg img-svg-50';
    return loadingAnimation;
  }
}

export const Util = new UtilClass();
