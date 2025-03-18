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

  createLoadingAnimation() {
    const loadingAnimation = new Image();
    loadingAnimation.src = '/img/loading.gif';
    loadingAnimation.alt = Global.get('globalLoading');
    loadingAnimation.className = 'img-svg img-svg-50';
    return loadingAnimation;
  }

  buildImageLoadingElement(className = '') {
    let container = document.createElement('div');
    container.className = 'loader-container' + (className.length ? ' ' + className : '');

    let loaderDiv = document.createElement('div');
    loaderDiv.className = 'loader';
    container.appendChild(loaderDiv);

    return container;
  }

  displaySearchResult(containerEl, results) {
    if (!results.length) {
      return;
    }

    let previousHeaderTitle = null;

    for (const i in results) {
      if (results[i].headerTitle.length && previousHeaderTitle !== results[i].headerTitle) {
        const titleEl = document.createElement('h1');
        titleEl.className = 'search-result-header';
        titleEl.textContent = results[i].headerTitle;
        containerEl.appendChild(titleEl);
      }

      const resultItem = document.createElement('a');
      resultItem.className = 'search-result-item';
      resultItem.dataset.itemId = results[i].id;
      resultItem.href = results[i].url;

      const resultContent = document.createElement('div');
      resultContent.className = 'search-result-content';

      if (results[i].media) {
        const resultMediaContainer = document.createElement('figure');
        resultMediaContainer.className = 'search-result-media';

        const resultMedia = new Image();
        resultMedia.src = results[i].media.pathXSmall;

        resultMediaContainer.appendChild(resultMedia);
        resultItem.appendChild(resultMediaContainer);
      }

      const resultTitle = document.createElement('div');
      resultTitle.textContent = results[i].title;
      resultTitle.className = 'search-result-title';
      resultContent.appendChild(resultTitle);

      if (results[i].subtitle) {
        const resultSubtitle = document.createElement('div');
        resultSubtitle.className = 'search-result-subtitle';
        resultSubtitle.textContent = results[i].subtitle;
        resultContent.appendChild(resultSubtitle);
      }

      resultItem.appendChild(resultContent);

      containerEl.appendChild(resultItem);

      previousHeaderTitle = results[i].headerTitle;
    }

    if (window.innerWidth <= 800) {
      document.activeElement.blur();
    }
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

  dataBase64ToFileObject(dataBase64, filename, lastModified) {
    const arr = dataBase64.split(',');
    const mime = arr[0].match(/:(.*?);/)[1];
    const bstr = atob(arr[arr.length - 1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);

    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }

    const options = {
      type: mime,
    };

    if (lastModified) {
      options.lastModified = lastModified;
    }

    return new File([u8arr], filename, options,);
  }
}

export const Util = new UtilClass();
