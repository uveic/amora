import {Global} from './localisation.js?v=000';

class UtilClass {
  getUpdatedAtTime() {
    const now = new Date();
    const prefix = ' ' + Global.get('globalAt') + ' ';

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

  notifyError(errorObj = null, errorMessage = null) {
    let feedbackDiv = document.querySelector('#feedback');

    if (!feedbackDiv) {
      feedbackDiv = document.createElement('div');
      feedbackDiv.id = 'feedback';
      feedbackDiv.className = 'feedback null';
      const main = document.querySelector('main');
      if (main) {
        main.insertAdjacentElement('afterbegin', feedbackDiv);
      } else {
        const bodyEl = document.querySelector('body');
        bodyEl.insertAdjacentElement('afterbegin', feedbackDiv);
      }
    }

    if (!feedbackDiv) {
      return;
    }

    feedbackDiv.textContent = errorMessage ?? (errorObj ? errorObj.message : Global.get('genericError'));
    feedbackDiv.classList.remove('feedback-success');
    feedbackDiv.classList.add('feedback-error');
    feedbackDiv.classList.remove('null');
    setTimeout(() => {feedbackDiv.classList.add('null')}, 5000);
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

  buildYoutubeIFrameElement(youtubeUrl) {
    const ytVideoId = this.getYoutubeVideoIdFromUrl(youtubeUrl);
    if (!ytVideoId) {
      return null;
    }

    const iframeElement = document.createElement('iframe');
    iframeElement.width = '560';
    iframeElement.height = '315';
    iframeElement.src = 'https://www.youtube-nocookie.com/embed/' + ytVideoId;
    iframeElement.title = 'Reprodutor de vídeo de YouTube';
    iframeElement.frameBorder = '0';
    iframeElement.allow = 'encrypted-media; picture-in-picture;';
    iframeElement.referrerpolicy = 'strict-origin-when-cross-origin';
    iframeElement.allowFullscreen = true;

    return iframeElement;
  }

  getAndCleanHtmlFromElement(element, addParagraph = false) {
    function cleanHtml(html) {
      if (!html || html === '-') {
        return '';
      }

      html = html.trim().replace(/^\s+|\s+$/gm, '');

      while (html.length && html.slice(-4) === '<br>') {
        html = html.slice(0, -4).trim();
      }

      return html;
    }

    if (!element) {
      return null;
    }

    function cleanElement(parentElement, childElement) {
      if (childElement.nodeName === '#text' && !childElement.textContent.trim().length) {
        return;
      }

      if (childElement.nodeName === 'DIV') {
        const newParagraph = document.createElement('p');
        newParagraph.innerHTML = childElement.innerHTML;
        parentElement.insertBefore(newParagraph, childElement);
        parentElement.removeChild(childElement);
        childElement = newParagraph;
      }

      if (addParagraph) {
        if (childElement.nodeName === '#text') {
          if (childElement.textContent.trim().length) {
            const newParagraph = document.createElement('p');
            newParagraph.textContent = childElement.textContent;
            parentElement.insertBefore(newParagraph, childElement);
          }

          parentElement.removeChild(childElement);
        }
      }

      const html = childElement.innerHTML ?? childElement.textContent;
      const currentHtml = cleanHtml(html);

      if (childElement.nodeName === 'BR') {
        childElement.parentNode.removeChild(childElement);
      } else if (!currentHtml.length) {
        parentElement.removeChild(childElement);
      }
    }

    element.childNodes.forEach(childElement => {
      cleanElement(element, childElement);
      childElement.childNodes.forEach(anotherChild => cleanElement(childElement, anotherChild));
    });

    return element.innerHTML.trim().length ? element.innerHTML.trim() : null;
  }

  displayFullPageLoadingModal() {
    let loadingModal = document.querySelector('.loading-modal');
    if (loadingModal) {
      loadingModal.classList.remove('null');
      return;
    }

    loadingModal = document.createElement('div');
    loadingModal.className = 'loading-modal';
    loadingModal.innerHTML = '<div class="loader"></div>';

    const main = document.querySelector('main');
    if (main) {
      main.appendChild(loadingModal);
      return;
    }

    document.body.appendChild(loadingModal);
  }

  hideFullPageLoadingModal() {
    let loadingModal = document.querySelector('.loading-modal');
    if (loadingModal) {
      loadingModal.classList.add('null');
    }
  }

  buildImageLoadingElement(className = '') {
    let container = document.createElement('div');
    container.className = 'loader-container' + (className.length ? ' ' + className : '');

    let loaderDiv = document.createElement('div');
    loaderDiv.className = 'loader';
    container.appendChild(loaderDiv);

    return container;
  }

  handleCopyLink(ev, href) {
    ev.preventDefault();

    if (!navigator.clipboard) {
      this.notifyError(new Error(Global.get('feedbackCopyLinkError')));
      return;
    }

    navigator.clipboard.writeText(href)
      .then(() => {
        this.notifyUser(Global.get('feedbackCopyLinkSuccess'));
      })
      .catch(error => this.notifyError(error, Global.get('feedbackCopyLinkError')));
  }

  generateRandomString(length = 16) {
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    let counter = 0;
    while (counter < length) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
      counter += 1;
    }
    return result;
  }

  highlightElement(el) {
    el.classList.add('background-highlight');
    setTimeout(() => {el.classList.remove('background-highlight')}, 5000);
  }
}

export const Util = new UtilClass();
