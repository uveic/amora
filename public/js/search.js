import {Request} from "./module/Request.js?v=000";
import {Global} from "./module/localisation.js?v=000";
import {Util} from "./module/Util.js?v=000";

const displaySearchResult = (containerEl, results) => {
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

    if (results[i].endpoint) {
      resultItem.dataset.endpoint = results[i].endpoint;
    }

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

function handleSearchKeyDownEvent(event) {
  const searchContainer = document.querySelector('#form-search');
  if (!searchContainer || searchContainer.classList.contains('null')) {
    return;
  }

  const resultContainer = document.querySelector('.search-result-container');

  if (event.key === 'ArrowDown') {
    event.preventDefault();
    const activeItem = resultContainer.querySelector('.search-result-item-active');
    if (activeItem) {
      const nextItem = resultContainer.querySelector('.search-result-item-active ~ .search-result-item');
      if (nextItem) {
        activeItem.classList.remove('search-result-item-active');
        nextItem.classList.add('search-result-item-active');
      } else {
        const firstItem = resultContainer.querySelector('.search-result-item');
        if (firstItem) {
          activeItem.classList.remove('search-result-item-active');
          firstItem.classList.add('search-result-item-active');
        }
      }
    } else {
      const firstItem = resultContainer.querySelector('.search-result-item');
      if (firstItem) {
        firstItem.classList.add('search-result-item-active');
      }
    }
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();

    const activeItem = resultContainer.querySelector('.search-result-item-active');
    if (!activeItem) {
      return;
    }

    let previousItem = null;
    for (const current of Array.from(resultContainer.querySelectorAll('.search-result-item'))) {
      if (current.classList.contains('search-result-item-active')) {
        if (previousItem) {
          activeItem.classList.remove('search-result-item-active');
          previousItem.classList.add('search-result-item-active');
        } else {
          const lastItem = resultContainer.querySelector('.search-result-item:last-of-type');
          if (lastItem) {
            activeItem.classList.remove('search-result-item-active');
            lastItem.classList.add('search-result-item-active');
          }
        }

        break;
      }

      previousItem = current;
    }
  } else if (event.key === 'Escape') {
    event.preventDefault();
    resultContainer.classList.add('null');
    const searchInput = document.querySelector('input[name="search"]');
    if (!searchInput.value.trim().length) {
      searchContainer.classList.add('null');
      document.querySelector('.search-fullscreen-shadow').classList.add('null');
      document.querySelector('.search-result-loading').classList.add('null');
    } else {
      searchInput.value = '';
      searchInput.focus();
    }
  } else if (event.key === 'Enter') {
    event.preventDefault();
    const activeItem = resultContainer.querySelector('.search-result-item-active');
    if (!resultContainer.classList.contains('null') && activeItem) {
      window.location = activeItem.href;
    } else {
      handleSearchRequest(event).then();
    }
  }
}

document.addEventListener('keydown', e => {
  if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
    return;
  }

  handleSearchKeyDownEvent(e);
});

async function handleSearchRequest(
  event,
  formIdentifier = 'form-search',
  resultContainerClass = 'search-result-container',
) {
  event.preventDefault();

  const form = document.querySelector('#' + formIdentifier);

  const loadingContainer = form.querySelector('.search-result-loading');
  if (loadingContainer) {
    loadingContainer.classList.remove('null');
  }

  const resultContainer = document.querySelector('.' + resultContainerClass);
  resultContainer.innerHTML = '';
  resultContainer.classList.add('null');

  const query = form.querySelector('input[type="search"]').value.trim();
  const isPublicPageEl = form.querySelector('input[name="searchFromPublicPage"]');
  const typeEl = form.querySelector('input[name="searchTypeId"]');

  let queryUrl = '/papi/search/?q=' + query;
  if (isPublicPageEl) {
    queryUrl += '&isPublic=' + isPublicPageEl.value;
  }

  if (typeEl) {
    queryUrl += typeEl.value.trim().length ? '&searchTypeId=' + typeEl.value.trim() : '';
  }

  return Request.get(queryUrl)
    .then((response) => {
      if (!response.results.length) {
        const resultNotFound = document.createElement('div');
        resultNotFound.className = 'search-result-item';
        resultNotFound.textContent = Global.get('feedbackSearchNotFound');

        resultContainer.appendChild(resultNotFound);

        if (loadingContainer) {
          loadingContainer.classList.add('null');
        }
        resultContainer.classList.remove('null');

        return;
      }

      displaySearchResult(resultContainer, response.results);

      if (loadingContainer) {
        loadingContainer.classList.add('null');
      }
      resultContainer.classList.remove('null');
    })
    .catch(error => {
      Util.notifyError(error);
      if (loadingContainer) {
        loadingContainer.classList.add('null');
      }
      resultContainer.classList.add('null');
    });
}

document.querySelectorAll('form#form-search').forEach(f => {
  f.addEventListener('submit', handleSearchRequest);
});

document.querySelectorAll('input[name="search"]').forEach(i => {
  i.addEventListener('input', () => {
    document.querySelector('.search-result-container').classList.add('null');
  });
});

document.querySelectorAll('.search-action-js').forEach(b => {
  b.addEventListener('click', () => {
    const form = document.querySelector('#form-search');
    const inputEl = form.querySelector('input[name="search"]');
    form.querySelector('.search-result-container').classList.add('null');
    inputEl.value = '';
    document.querySelector('.search-fullscreen-shadow').classList.remove('null');
    document.querySelector('#mobile-nav').checked = false;
    form.classList.remove('null');
    inputEl.focus();
  });
});

document.querySelectorAll('.search-close-js').forEach(b => {
  b.addEventListener('click', () => {
    document.querySelector('#form-search').classList.add('null');
    document.querySelector('.search-fullscreen-shadow').classList.add('null');
    document.querySelector('.search-result-loading').classList.add('null');
  });
});

document.querySelectorAll('.search-fullscreen-shadow').forEach(b => {
  b.addEventListener('click', () => {
    document.querySelector('#form-search').classList.add('null');
    document.querySelector('.search-fullscreen-shadow').classList.add('null');
    document.querySelector('.search-result-loading').classList.add('null');
  });
});

export {handleSearchRequest, displaySearchResult};