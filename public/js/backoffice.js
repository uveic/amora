import {Util} from './module/Util.js?v=000';
import {Request} from './module/Request.js?v=000';
import {Global} from "./module/localisation.js?v=000";
import {Uploader} from "./module/Uploader.js?v=000";

window.data = {
  mediaCache: [],
  mediaCacheRight: [],
  mediaCacheLeft: [],
}

function addEventListenerAction(media, mediaId, eventListenerAction, targetContainerId) {
  if (eventListenerAction === 'displayNextImagePopup') {
    media.addEventListener('click', displayNextImagePopup);
    media.removeEventListener('click', insertImageInArticle);
    media.removeEventListener('click', handleGenericMainMediaClick);
    media.removeEventListener('click', collectionAddMedia);
  } else if (eventListenerAction === 'insertImageInArticle') {
    media.removeEventListener('click', displayNextImagePopup);
    media.addEventListener('click', insertImageInArticle);
    media.removeEventListener('click', handleGenericMainMediaClick);
    media.removeEventListener('click', collectionAddMedia);
  } else if (eventListenerAction === 'handleGenericMainMediaClick') {
    media.removeEventListener('click', displayNextImagePopup);
    media.removeEventListener('click', insertImageInArticle);
    media.addEventListener('click', handleGenericMainMediaClick);
    media.removeEventListener('click', collectionAddMedia);
  } else if (eventListenerAction === 'collectionAddMedia') {
    media.removeEventListener('click', displayNextImagePopup);
    media.removeEventListener('click', insertImageInArticle);
    media.removeEventListener('click', handleGenericMainMediaClick);
    media.addEventListener('click', collectionAddMedia);
  } else {
    media.removeEventListener('click', displayNextImagePopup);
    media.removeEventListener('click', insertImageInArticle);
    media.removeEventListener('click', handleGenericMainMediaClick);
    media.removeEventListener('click', collectionAddMedia);
  }

  media.targetContainerId = targetContainerId;
  media.mediaId = mediaId;
}

function handleGenericSelectMainMediaClick(e) {
  e.preventDefault();

  const button = e.currentTarget;

  Util.displayFullPageLoadingModal();

  const modal = document.querySelector('.select-media-modal');
  const imagesContainer = modal.querySelector('#images-list');
  const loadMoreButton = modal.querySelector('.media-load-more-js');
  const uploadMediaButton = modal.querySelector('input[name="select-media-action-upload"]');
  const eventListenerAction = button.dataset.eventListenerAction;
  const targetContainerId = button.dataset.targetContainerId ?? null;
  uploadMediaButton.dataset.eventListenerAction = eventListenerAction;
  uploadMediaButton.dataset.targetContainerId = targetContainerId;
  loadMoreButton.dataset.eventListenerAction = eventListenerAction;
  loadMoreButton.dataset.targetContainerId = targetContainerId;
  modal.querySelector('.add-image-wrapper').classList.remove('null');
  modal.classList.remove('null');

  const qty = Number.parseInt(modal.dataset.mediaQueryQty);
  const existingImages = imagesContainer.querySelectorAll('.media-item');
  existingImages.forEach(img => {
    addEventListenerAction(img, img.dataset.mediaId, eventListenerAction, targetContainerId);
  });

  if (existingImages.length) {
    Util.hideFullPageLoadingModal();
    return;
  }

  const typeId = button.dataset.typeId ? Number.parseInt(button.dataset.typeId) : '';

  Request.get('/api/file?typeId=' + typeId + '&qty=' + qty)
    .then(response => {
      imagesContainer.classList.remove('null');
      displayImageFromApiCall(imagesContainer, response.files, eventListenerAction, targetContainerId);
    })
    .catch(error => {
      modal.classList.add('null');
      Util.notifyError(error);
    })
    .finally(() => Util.hideFullPageLoadingModal());
}

function handleGenericMainMediaClick(e) {
  e.preventDefault();

  const mediaId = e.currentTarget.mediaId;
  const targetContainerId = e.currentTarget.targetContainerId;

  const mediaContainer = document.querySelector('#' + targetContainerId);
  const targetImg = mediaContainer.querySelector('.media-item');
  const sourceImg = document.querySelector('img[data-media-id="' + mediaId + '"]');

  if (targetImg) {
    targetImg.src = sourceImg.dataset.pathLarge;
    targetImg.alt = sourceImg.alt;
    targetImg.title = sourceImg.title;
    targetImg.dataset.mediaId = sourceImg.dataset.mediaId;
    targetImg.srcset = sourceImg.srcset;
    targetImg.className = 'media-item';
  } else {
    const newImage = new Image();
    newImage.src = sourceImg.dataset.pathLarge;
    newImage.alt = sourceImg.alt;
    newImage.title = sourceImg.title;
    newImage.dataset.mediaId = sourceImg.dataset.mediaId;
    newImage.srcset = sourceImg.srcset;
    newImage.className = 'media-item';
    mediaContainer.appendChild(newImage);
  }

  e.currentTarget.textContent = Global.get('globalModify');
  document.querySelector('.select-media-modal').classList.add('null');
  const deleteButtonEl = mediaContainer.querySelector('.generic-media-delete-js');
  if (deleteButtonEl) {
    deleteButtonEl.classList.remove('null');
  }
  mediaContainer.scrollIntoView({behavior: 'smooth', block: 'start'});
}

function handleGenericMediaDeleteClick(e) {
  e.preventDefault();

  const b = e.currentTarget;

  const delRes = window.confirm(Global.get('feedbackDeleteGeneric'));
  if (!delRes) {
    return;
  }

  b.parentElement.parentElement.removeChild(b.parentElement.parentElement.querySelector('.media-item'));
  b.classList.add('null');
}

function addMediaToModalContainer(existingModalContainer, mediaId) {
  const existingMedia = document.querySelector('img[data-media-id="' + mediaId + '"]');

  if (!existingMedia) {
    return;
  }

  existingModalContainer.querySelectorAll('figure .null').forEach(i => {
    i.classList.remove('null');
  });

  const existingInModal = existingModalContainer.querySelector('img[data-media-id="' + mediaId + '"]');
  if (existingInModal) {
    existingModalContainer.parentElement.classList.add('null');
  }

  existingModalContainer.querySelectorAll('.media-dynamically-added').forEach(i => {
    existingModalContainer.removeChild(i.parentElement);
  });

  const figureContainer = document.createElement('figure');
  figureContainer.className = 'image-container';

  const newImage = new Image();
  newImage.src = existingMedia.src;
  newImage.alt = existingMedia.alt;
  newImage.title = existingMedia.title;
  newImage.dataset.mediaId = mediaId;
  newImage.dataset.pathLarge = existingMedia.src;
  newImage.className = 'media-item media-dynamically-added';
  figureContainer.appendChild(newImage);

  if (existingModalContainer.firstChild) {
    existingModalContainer.insertBefore(figureContainer, existingModalContainer.firstChild);
  } else {
    existingModalContainer.appendChild(figureContainer);
  }
}

function displayModalImage(image) {
  const modalContainer = document.querySelector('.modal-media');
  const imageWrapper = modalContainer.querySelector('.image-wrapper');
  let imageElement = modalContainer.querySelector('.image-main img');
  const imageInfoData = modalContainer.querySelector('.image-info-data');
  const imageInfoNext = modalContainer.querySelector('.image-next-wrapper');
  const loaderContainer = modalContainer.querySelector('.loader-media');

  if (!imageElement) {
    imageElement = new Image();
    modalContainer.querySelector('.image-main').appendChild(imageElement);
  }

  if (!image) {
    document.querySelectorAll('.image-next-action').forEach(i => i.classList.add('hidden'));
    imageWrapper.classList.remove('null');
    imageWrapper.classList.remove('filter-opacity');
    imageInfoData.classList.remove('null');
    imageInfoNext.classList.remove('null');
    loaderContainer.classList.add('null');

    return;
  }

  const alt = image.caption ?? image.name;
  imageElement.src = image.pathLarge;
  imageElement.alt = alt;
  imageElement.title = alt;
  imageElement.width = image.width;
  imageElement.height = image.height;
  imageElement.dataset.mediaId = image.id;
  imageElement.addEventListener('load', () => {
    imageElement.classList.remove('hidden');
    imageWrapper.classList.remove('filter-opacity');
    loaderContainer.classList.add('null');
    imageInfoData.classList.remove('hidden');
    imageInfoNext.classList.remove('hidden');
    imageWrapper.scrollIntoView({behavior: 'smooth', block: 'start'});
  });

  // Hide/display image nav buttons
  const firstImageEl = document.querySelector('#images-list .media-item');
  const firstImageId = firstImageEl ? Number.parseInt(firstImageEl.dataset.mediaId) : null;

  if (firstImageId === image.id) {
    document.querySelectorAll('.image-previous-action').forEach(i => i.classList.add('hidden'));
  } else {
    document.querySelectorAll('.image-previous-action').forEach(i => i.classList.remove('hidden'));
  }

  document.querySelectorAll('.image-next-action').forEach(i => i.classList.remove('hidden'));

  modalContainer.querySelector('.image-number').textContent = '#' + image.id;
  modalContainer.querySelector('.image-caption').textContent = image.caption;
  modalContainer.querySelector('.image-meta').innerHTML = image.exifHtml;

  const copyLinkEl = modalContainer.querySelector('.image-meta .copy-link');
  if (copyLinkEl) {
    copyLinkEl.addEventListener('click', e => Util.handleCopyLink(e, image.pathLarge));
  }

  const imageDeleteEl = modalContainer.querySelector('.image-delete');
  imageDeleteEl.dataset.mediaId = image.id;
  const appearsOnContainer = modalContainer.querySelector('.image-appears-on');
  appearsOnContainer.innerHTML = '';
  if (image.appearsOn && image.appearsOn.length) {
    imageDeleteEl.classList.add('null');

    const appearsTitle = document.createElement('h3');
    appearsTitle.textContent = Global.get('globalAppearsOn') + ':';
    appearsOnContainer.appendChild(appearsTitle);

    image.appearsOn.forEach(ao => {
      const appearsLink = document.createElement('a');
      appearsLink.href = ao.path;
      appearsLink.target = '_blank';
      appearsLink.textContent = ao.title;
      const appearsInfo = document.createElement('span');
      appearsInfo.innerHTML = '<img src="/img/svg/calendar-white.svg" class="img-svg m-r-025" alt="Calendar">' +
        Global.formatDate(new Date(ao.publishedOn), false, false, true, false, false);
      appearsLink.appendChild(appearsInfo);
      appearsOnContainer.appendChild(appearsLink);
    });
  } else {
    imageDeleteEl.classList.remove('null');
  }

  imageInfoData.classList.remove('null');
  imageInfoNext.classList.remove('null');

  imageWrapper.classList.remove('null');
}

function preloadMedia(mediaId, direction) {
  if (!mediaId) {
    return;
  }

  const mediaIdToPreload = direction === 'DESC' ? window.data.mediaCacheRight[mediaId] : window.data.mediaCacheLeft[mediaId];

  if (!mediaIdToPreload) {
    return;
  }

  const mediaObj = window.data.mediaCache[mediaIdToPreload];
  if (!mediaObj) {
    return;
  }

  const imgTemp = new Image();
  imgTemp.src = mediaObj.pathLarge;
}

function updateMediaCache(medias, direction) {
  let previousId = null;

  medias.forEach(item => {
    if (!window.data.mediaCache[item.id]) {
      window.data.mediaCache[item.id] = item;
    }

    if (previousId) {
      if (direction === 'ASC') {
        window.data.mediaCacheLeft[previousId] = item.id;
        window.data.mediaCacheRight[item.id] = previousId;
      } else if (direction === 'DESC') {
        window.data.mediaCacheLeft[item.id] = previousId;
        window.data.mediaCacheRight[previousId] = item.id;
      }
    }

    previousId = item.id;
  });
}

async function modalRetrieveMediaAndAddToCache(mediaId, direction) {
  if (direction === 'DESC' || direction === 'ASC') {
    const fourthNextMediaId = getFourthNextMediaId(mediaId, direction);
    if (fourthNextMediaId) {
      return window.data.mediaCache[mediaId];
    }
  }

  const qty = direction === 'DESC' || direction === 'ASC' ? 20 : 1;
  const typeId = document.querySelector('.media-load-more-js').dataset.typeId;
  const apiUrl = '/api/file/' + mediaId + '?direction=' + direction + '&typeId=' + typeId + '&qty=' + qty;

  return Request.get(apiUrl)
    .then(response => {
      updateMediaCache(response.files, direction);
      return direction === 'DESC' || direction === 'ASC' ? (response.files[1] ?? null) : (response.files[0] ?? null);
    });
}

function getFourthNextMediaId(mediaId, direction) {
  const nextMediaId = direction === 'DESC' ? window.data.mediaCacheRight[mediaId] : window.data.mediaCacheLeft[mediaId];

  const secondNextMediaId = nextMediaId ?
    (direction === 'DESC' ? window.data.mediaCacheRight[nextMediaId] : window.data.mediaCacheLeft[nextMediaId])
    : null;

  const thirdNextMediaId = secondNextMediaId ?
    (direction === 'DESC' ? window.data.mediaCacheRight[secondNextMediaId] : window.data.mediaCacheLeft[secondNextMediaId])
    : null;

  return thirdNextMediaId ?
    (direction === 'DESC' ? window.data.mediaCacheRight[thirdNextMediaId] : window.data.mediaCacheLeft[thirdNextMediaId])
    : null;
}

async function modalGetMedia(mediaId, direction) {
  if (window.data.mediaCache[mediaId]) {
    return window.data.mediaCache[mediaId];
  }

  const typeId = document.querySelector('.media-load-more-js').dataset.typeId;

  return Request.get('/api/file/' + mediaId + '?direction=' + direction + '&typeId=' + typeId + '&qty=20')
    .then(response => {
      updateMediaCache(response.files, direction);
      return response.files[0] ?? null;
    });
}

async function modalGetNextMedia(currentMediaId, direction) {
  let nextMediaId = null;
  if (direction === 'DESC' && window.data.mediaCacheRight[currentMediaId]) {
    nextMediaId = window.data.mediaCacheRight[currentMediaId];
  } else if (direction === 'ASC' && window.data.mediaCacheLeft[currentMediaId]) {
    nextMediaId = window.data.mediaCacheLeft[currentMediaId];
  }

  if (nextMediaId && window.data.mediaCache[nextMediaId]) {
    return window.data.mediaCache[nextMediaId];
  }

  return modalRetrieveMediaAndAddToCache(currentMediaId, direction)
    .then(mediaObj => {
      if (!mediaObj) {
        return null;
      }

      const newMedia = new Image();
      newMedia.src = mediaObj.pathLarge;

      return new Promise((resolve, reject) => {
        newMedia.onload = () => resolve(mediaObj);
        newMedia.onerror = reject;
      });
    });
}

function displayNextImagePopup(e) {
  e.preventDefault();

  const mediaId = e.currentTarget.mediaId;
  const next = e.currentTarget.next ?? false;
  const direction = e.currentTarget.direction ?? 'DESC';

  const modalContainer = document.querySelector('.modal-media');
  const imageWrapper = modalContainer.querySelector('.image-wrapper');

  modalContainer.classList.remove('null');
  imageWrapper.classList.add('filter-opacity');
  imageWrapper.classList.remove('null');
  modalContainer.querySelector('.loader-media').classList.remove('null');

  if (next) {
    modalGetNextMedia(mediaId, direction)
      .then(mediaObj => {
        displayModalImage(mediaObj);
        preloadMedia(mediaObj ? mediaObj.id : null, direction);
      }).then(() => {
      if (direction === 'DESC' || direction === 'ASC') {
        modalRetrieveMediaAndAddToCache(mediaId, direction)
          .then();
      }
    });

    return;
  }

  const imageEl = imageWrapper.querySelector('.image-main img');
  if (imageEl) {
    imageEl.classList.add('hidden');
  }
  imageWrapper.querySelector('.image-info-data').classList.add('hidden');
  modalContainer.querySelector('.image-next-wrapper').classList.add('hidden');

  modalGetMedia(mediaId, direction)
    .then(mediaObj => {
      displayModalImage(mediaObj);
      preloadMedia(mediaObj.id, direction);
    });
}

function insertImageInArticle(e) {
  const container = document.querySelector('.trix-editor-content');
  const mediaId = e.currentTarget.mediaId;
  const existingImage = document.querySelector('img[data-media-id="' + mediaId + '"]');

  const newImage = new Image();
  newImage.className = 'media-item';
  newImage.src = existingImage.dataset.pathLarge;
  newImage.dataset.mediaId = existingImage.dataset.mediaId;
  newImage.alt = existingImage.alt;
  newImage.srcset = existingImage.srcset;
  newImage.sizes = existingImage.sizes;
  newImage.title = existingImage.title;
  newImage.width = existingImage.width;
  newImage.height = existingImage.height;
  newImage.loading = 'lazy';
  container.appendChild(newImage);

  document.querySelector('.select-media-modal').classList.add('null');

  newImage.scrollIntoView({behavior: 'smooth', block: 'start'});
}

function collectionAddMedia(e) {
  const mediaId = e.currentTarget.mediaId;
  const targetContainerId = e.currentTarget.targetContainerId;

  const afterResponse = (response, isMainMedia) => {
    if (response.collectionId) {
      container.dataset.collectionId = response.collectionId;

      const collectionMediaContainer = document.querySelector('.collection-item-media');
      if (collectionMediaContainer) {
        collectionMediaContainer.dataset.collectionId = response.collectionId;
      }
    }

    if (response.html) {
      if (isMainMedia) {
        const existing = container.querySelector('.media-item');
        if (existing) {
          container.removeChild(existing);
        }
        container.insertAdjacentHTML('afterbegin', response.html);
        container.classList.remove('no-image-simple');
        container.parentElement.querySelector('.collection-main-media-delete-js').classList.remove('null');
        container.parentElement.querySelector('.button-media-add span').textContent = Global.get('globalModify');
      } else {
        const buttonMediaAdd = container.querySelector('.button-media-add');
        buttonMediaAdd.insertAdjacentHTML('beforebegin', response.html);
      }

      const countEl = container.parentElement.querySelector('.collection-item-media-header .count');
      if (countEl) {
        countEl.textContent = (Number.parseInt(countEl.textContent) + 1).toString();
      }

      const deleteMediaEl = container.querySelector('.collection-media-delete-js[data-media-id="' + mediaId + '"]');
      if (deleteMediaEl) {
        deleteMediaEl.targetContainerId = targetContainerId;
        deleteMediaEl.mediaId = mediaId;
        deleteMediaEl.addEventListener('click', collectionDeleteMedia);
      }

      const editMediaEl = container.querySelector('.collection-media-caption-js[data-media-id="' + mediaId + '"]');
      if (editMediaEl) {
        editMediaEl.mediaId = mediaId;
        editMediaEl.addEventListener('click', collectionEditMediaCaption);
      }

      if (!isMainMedia) {
        const newMediaEl = container.querySelector('.item-draggable:last-of-type .media-item');
        newMediaEl.addEventListener('dragstart', handleAlbumMediaDragStart);
        newMediaEl.addEventListener('dragleave', handleAlbumMediaDragLeave);
        newMediaEl.addEventListener('dragend', handleAlbumMediaDragEnd);
        newMediaEl.addEventListener('drop', handleAlbumMediaDrop);
      }
    } else {
      Util.notifyUser(Global.get('collectionExistingImageWarning'));
    }
  }

  const container = document.querySelector('#' + targetContainerId);
  const collectionId = container.dataset.collectionId;
  const isMainMedia = Number.parseInt(container.dataset.isMainMedia) === 1;

  Util.displayFullPageLoadingModal();

  const payload = {
    titleHtml: null,
    contentHtml: null,
    mediaId: mediaId,
    isMainMedia: isMainMedia,
  };

  document.querySelector('.select-media-modal').classList.add('null');

  if (collectionId) {
    Request.post('/back/collection/' + collectionId + '/media', JSON.stringify(payload))
      .then(response => afterResponse(response, isMainMedia))
      .catch(error => {
        if (isMainMedia) {
          container.removeChild(container.querySelector('.media-item'));
        } else {
          container.removeChild(container.querySelector('.collection-media-container[data-media-id="' + mediaId + '"]'));
        }
        Util.notifyError(error);
      })
      .finally(() => Util.hideFullPageLoadingModal());
  } else {
    Request.post('/back/collection/media', JSON.stringify(payload))
      .then(response => afterResponse(response, isMainMedia))
      .catch(error => {
        if (isMainMedia) {
          container.removeChild(container.querySelector('.media-item'));
        } else {
          container.removeChild(container.querySelector('.collection-media-container[data-media-id="' + mediaId + '"]'));
        }
        Util.notifyError(error);
      })
      .finally(() => Util.hideFullPageLoadingModal());
  }
}

function editCollection(e) {
  e.preventDefault();

  const collectionId = e.currentTarget.dataset.collectionId;

  document.querySelectorAll('.collection-item').forEach(s => {
    const otherCollectionId = s.dataset.collectionId;
    if (collectionId !== otherCollectionId) {
      makeCollectionNonEditable(otherCollectionId);
    }
  });

  const container = document.querySelector('.collection-item[data-collection-id="' + collectionId + '"]');
  const titleEl = container.querySelector('.collection-title-html');
  const subtitleEl = container.querySelector('.collection-subtitle-html');
  const contentEl = container.querySelector('.collection-content-html');
  const sequenceEl = container.querySelector('.collection-sequence');
  const sequenceValue = Number.parseInt(sequenceEl.dataset.before);

  if (titleEl.textContent.trim() === '-') {
    titleEl.textContent = '';
  }

  if (subtitleEl.textContent.trim() === '-') {
    subtitleEl.textContent = '';
  }

  if (contentEl.textContent.trim() === '-') {
    contentEl.textContent = '';
  }

  container.querySelectorAll('.collection-label').forEach(sl => sl.classList.remove('null'));
  titleEl.classList.add('m-t-0');
  subtitleEl.classList.add('m-t-0');
  contentEl.classList.add('m-t-0');
  sequenceEl.classList.add('m-t-0');

  titleEl.contentEditable = true;
  titleEl.classList.add('album-content-editable');
  subtitleEl.contentEditable = true;
  subtitleEl.classList.add('album-content-editable');
  contentEl.contentEditable = true;
  contentEl.classList.add('album-content-editable');

  if (sequenceValue > 0) {
    sequenceEl.contentEditable = true;
    sequenceEl.classList.add('album-content-editable');
    sequenceEl.textContent = sequenceEl.textContent.trim().replace('#', '');
  }

  container.querySelector('.main-image-button-container').classList.remove('null');
  container.querySelector('.collection-button-container-js').classList.remove('null');
  container.querySelector('.main-image-container').classList.remove('null');
  const mainMedia = container.querySelector('.main-image-container .media-item');
  if (mainMedia && !mainMedia.classList.contains('null')) {
    container.querySelector('.generic-media-delete-js').classList.remove('null');
  } else {
    container.querySelector('.generic-media-delete-js').classList.add('null');
  }

  e.currentTarget.classList.add('null');
  titleEl.focus();

  container.scrollIntoView({behavior: 'smooth', block: 'start'});
}

function updateCollection(e) {
  e.preventDefault();

  const collectionId = e.currentTarget.dataset.collectionId;

  makeCollectionNonEditable(collectionId);

  const container = document.querySelector('.collection-item[data-collection-id="' + collectionId + '"]');

  const titleHtmlEl = container.querySelector('.collection-title-html');
  const titleHtml = titleHtmlEl.textContent.trim();
  titleHtmlEl.dataset.before = titleHtml;

  const subtitleHtmlEl = container.querySelector('.collection-subtitle-html');
  const subtitleHtml = subtitleHtmlEl.textContent.trim();
  subtitleHtmlEl.dataset.before = subtitleHtml;

  const contentHtmlEl = container.querySelector('.collection-content-html');
  const contentHtmlBeforeEl = container.querySelector('.collection-content-html-before');
  const contentHtml = Util.getAndCleanHtmlFromElement(contentHtmlEl);
  contentHtmlBeforeEl.innerHTML = contentHtml ?? '';

  const mainMedia = container.querySelector('img.media-item');
  const mainMediaId = mainMedia && !mainMedia.classList.contains('null') ?
    Number.parseInt(mainMedia.dataset.mediaId)
    : null;
  container.querySelector('.main-image-container').dataset.before = mainMediaId ?? '';

  const sequenceEl = container.querySelector('.collection-sequence');
  const sequenceRaw = sequenceEl.textContent.trim().replace('#', '');
  const sequence = Number.isNaN(sequenceRaw) ? Number.parseInt(sequenceEl.dataset.before)
    : Number.parseInt(sequenceEl.textContent.trim().replace('#', ''));

  const targetCollectionId = updateCollectionSequences(
    Number.parseInt(sequenceEl.dataset.before),
    sequence,
  );

  const payload = {
    titleHtml: titleHtml === '-' ? null : titleHtml,
    subtitleHtml: subtitleHtml === '-' ? null : subtitleHtml,
    contentHtml: contentHtml,
    mainMediaId: mainMediaId,
    newSequence: sequence,
    collectionIdSequenceTo: targetCollectionId,
  };

  Request.put('/back/collection/' + collectionId, JSON.stringify(payload))
    .then(() => {
      if (mainMedia && mainMedia.classList.contains('null')) {
        mainMedia.parentElement.removeChild(mainMedia);
      }
    })
    .catch(error => {
      Util.notifyError(error);
    });

  container.scrollIntoView({behavior: 'smooth', block: 'start'});
}

function makeCollectionNonEditable(collectionId) {
  const container = document.querySelector('.collection-item[data-collection-id="' + collectionId + '"]');
  const titleEl = container.querySelector('.collection-title-html');
  const subtitleEl = container.querySelector('.collection-subtitle-html');
  const contentEl = container.querySelector('.collection-content-html');
  const mediaButtonsContainer = container.querySelector('.main-image-button-container');
  const editButton = container.querySelector('.collection-edit-js');
  const sequenceEl = container.querySelector('.collection-sequence');

  if (titleEl.textContent.trim() === '') {
    titleEl.textContent = '-';
  }

  if (subtitleEl.textContent.trim() === '') {
    subtitleEl.textContent = '-';
  }

  if (contentEl.textContent.trim() === '') {
    contentEl.textContent = '-';
  }

  container.querySelectorAll('.collection-label').forEach(sl => sl.classList.add('null'));
  titleEl.classList.remove('m-t-0');
  subtitleEl.classList.remove('m-t-0');
  contentEl.classList.remove('m-t-0');
  sequenceEl.classList.remove('m-t-0');

  titleEl.contentEditable = false;
  titleEl.classList.remove('album-content-editable');
  subtitleEl.contentEditable = false;
  subtitleEl.classList.remove('album-content-editable');
  contentEl.contentEditable = false;
  contentEl.classList.remove('album-content-editable');
  sequenceEl.contentEditable = false;
  sequenceEl.classList.remove('album-content-editable');
  if (!sequenceEl.textContent.includes('#')) {
    sequenceEl.textContent = '#' + sequenceEl.textContent;
  }
  mediaButtonsContainer.classList.add('null');
  editButton.classList.remove('null');
  container.querySelector('.collection-button-container-js').classList.add('null');
}

function cancelCollectionEdit(e) {
  e.preventDefault();

  const collectionId = e.currentTarget.dataset.collectionId;

  const container = document.querySelector('.collection-item[data-collection-id="' + collectionId + '"]');
  const titleHtmlEl = container.querySelector('.collection-title-html');
  titleHtmlEl.textContent = titleHtmlEl.dataset.before;
  const subtitleHtmlEl = container.querySelector('.collection-subtitle-html');
  subtitleHtmlEl.textContent = subtitleHtmlEl.dataset.before;
  const contentHtmlEl = container.querySelector('.collection-content-html');
  const contentHtmlBeforeEl = container.querySelector('.collection-content-html-before');
  contentHtmlEl.innerHTML = contentHtmlBeforeEl.innerHTML;
  container.querySelector('.main-image-container').classList.remove('null');
  container.querySelector('.generic-media-delete-js').classList.remove('null');

  const mainMedia = container.querySelector('.main-image-container .media-item');
  if (mainMedia) {
    if (mainMedia.parentElement.dataset.before &&
      mainMedia.parentElement.dataset.before !== mainMedia.dataset.mediaId
    ) {
      const originalMedia = document.querySelector('img[data-media-id="' + mainMedia.parentElement.dataset.before + '"]');
      if (originalMedia) {
        mainMedia.src = originalMedia.src;
        mainMedia.dataset.mediaId = originalMedia.dataset.mediaId;
        mainMedia.alt = originalMedia.alt;
        mainMedia.title = originalMedia.title;
      }
    }

    if (mainMedia.parentElement.dataset.before) {
      mainMedia.classList.remove('null');
    } else {
      mainMedia.classList.add('null');
    }

    if (mainMedia.classList.contains('null')) {
      container.querySelector('.select-media-action span').textContent = Global.get('globalSelectImage');
    } else {
      container.querySelector('.select-media-action span').textContent = Global.get('globalModify');
    }
  }

  const sequenceEl = container.querySelector('.collection-sequence');
  sequenceEl.textContent = '#' + sequenceEl.dataset.before;

  makeCollectionNonEditable(collectionId);
  container.scrollIntoView({behavior: 'smooth', block: 'start'});
}

function collectionDeleteMedia(e) {
  e.preventDefault();

  const delRes = window.confirm(Global.get('feedbackDeleteGeneric'));
  if (!delRes) {
    return;
  }

  const mediaId = e.currentTarget.mediaId;
  const collectionMediaId = e.currentTarget.dataset.collectionMediaId;
  const targetContainerId = e.currentTarget.targetContainerId;

  const container = document.querySelector('#' + targetContainerId);
  const targetMediaContainer = container.querySelector('img[data-media-id="' + mediaId + '"]');

  targetMediaContainer.parentElement.parentElement.classList.add('null');

  const countEl = container.parentElement.querySelector('.collection-item-media-header .count');
  if (countEl) {
    countEl.textContent = (Number.parseInt(countEl.textContent) - 1).toString();
  }

  const deletedMediaSequence = Number.parseInt(targetMediaContainer.dataset.sequence);
  container.querySelectorAll('.media-item').forEach(mi => {
    const currentSequence = Number.parseInt(mi.dataset.sequence);
    if (currentSequence > deletedMediaSequence) {
      mi.dataset.sequence = (currentSequence - 1).toString();
    }
  });

  Request.delete('/back/collection-media/' + collectionMediaId)
    .catch(error => {
      targetMediaContainer.parentElement.parentElement.classList.remove('null');
      Util.notifyError(error);

      if (countEl) {
        countEl.textContent = (Number.parseInt(countEl.textContent) + 1).toString();
      }

      container.querySelectorAll('.media-item').forEach(mi => {
        const currentSequence = Number.parseInt(mi.dataset.sequence);
        const currentMediaId = mi.dataset.mediaId;
        if (currentSequence >= deletedMediaSequence && mediaId !== currentMediaId) {
          mi.dataset.sequence = (currentSequence + 1).toString();
        }
      });
    });
}

function collectionDeleteMainMedia(e) {
  e.preventDefault();

  const delRes = window.confirm(Global.get('feedbackDeleteGeneric'));
  if (!delRes) {
    return;
  }

  const collectionId = e.currentTarget.dataset.collectionId;
  const targetContainerId = e.currentTarget.dataset.targetContainerId;

  const targetContainer = document.querySelector('#' + targetContainerId);
  const targetMediaContainer = targetContainer.querySelector('.media-item');
  targetMediaContainer.classList.add('null');

  Request.delete('/back/collection/' + collectionId + '/main-media')
    .then(() => {
      targetMediaContainer.parentElement.removeChild(targetMediaContainer);
      targetContainer.classList.add('no-image-simple');
      targetContainer.parentElement.querySelector('.button-media-add span').textContent = Global.get('globalSelectImage');
      targetContainer.parentElement.querySelector('.collection-main-media-delete-js').classList.add('null');
    })
    .catch(error => {
      targetMediaContainer.classList.remove('null');
      Util.notifyError(error);
    });
}

function collectionEditMediaCaption(e) {
  e.preventDefault();

  const collectionMediaId = e.currentTarget.dataset.collectionMediaId;
  const collectionId = e.currentTarget.dataset.collectionId;
  const mediaId = e.currentTarget.mediaId;
  const modal = document.querySelector('.album-media-caption-edit-modal-js');
  const mediaContainer = modal.querySelector('.album-media-edit-container');
  mediaContainer.querySelectorAll('img').forEach(i => mediaContainer.removeChild(i));
  modal.querySelector('input[name="collectionMediaId"]').value = collectionMediaId;
  const existingMedia = e.currentTarget.parentElement.parentElement.querySelector('img[data-media-id="' + mediaId + '"]');
  const collectionContentContainer = document.querySelector(
    '.collection-item[data-collection-id="' + collectionId + '"]'
  );
  const titleText = collectionContentContainer.querySelector('.collection-title-html').textContent;
  const subtitleText = collectionContentContainer.querySelector('.collection-sequence').textContent +
    (titleText === '-' ? '' : (' ' + titleText));

  const newMediaEl = new Image();
  newMediaEl.src = existingMedia.src;
  newMediaEl.alt = existingMedia.alt;
  newMediaEl.title = existingMedia.title;
  mediaContainer.appendChild(newMediaEl);

  const htmlContainer = modal.querySelector('.media-caption-html');
  htmlContainer.innerHTML = e.currentTarget.textContent === '-' ? '' : e.currentTarget.textContent;
  modal.querySelector('.modal-header-subtitle').textContent = subtitleText;

  modal.classList.remove('null');
  htmlContainer.focus();
}

function updateCollectionSequences(sourceSequence, targetSequence) {
  if (sourceSequence === targetSequence) {
    return;
  }

  const container = document.querySelector('.collections-wrapper');
  const sourceCollection = container.querySelector('.collection-item[data-sequence="' + sourceSequence + '"]');
  let targetCollection = container.querySelector('.collection-item[data-sequence="' + targetSequence + '"]');
  if (!targetCollection) {
    let closestSequence = null;
    const sequences = [];
    container.querySelectorAll('.collection-item').forEach(asi => sequences.push(Number.parseInt(asi.dataset.sequence)));

    targetCollection = closestSequence ?
      container.querySelector('.collection-item[data-sequence="' + closestSequence + '"]')
      : container.querySelector('.collection-item:last-of-type');

    targetSequence = Number.parseInt(targetCollection.dataset.sequence);
  }

  const currentCollectionEl = sourceCollection.querySelector('.collection-sequence');
  currentCollectionEl.textContent = '#' + targetSequence.toString();
  currentCollectionEl.dataset.before = targetSequence.toString();
  sourceCollection.dataset.sequence = targetSequence.toString();

  let countDelta = 0;
  if (targetSequence < sourceSequence) {
    container.insertBefore(sourceCollection, targetCollection);
    countDelta = 1;
  } else {
    if (targetCollection.nextSibling) {
      container.insertBefore(sourceCollection, targetCollection.nextSibling);
    } else {
      container.insertAdjacentElement('beforeend', sourceCollection);
    }

    countDelta = -1;
  }

  container.querySelectorAll('.collection-item').forEach(asi => {
    if (asi.dataset.collectionId === sourceCollection.dataset.collectionId) {
      return;
    }

    const currentSequence = Number.parseInt(asi.dataset.sequence);
    if (
      (currentSequence >= targetSequence && currentSequence < sourceSequence) ||
      (currentSequence <= targetSequence && currentSequence > sourceSequence)
    ) {
      asi.dataset.sequence = (currentSequence + countDelta).toString();
      const asiSequenceEl = asi.querySelector('.collection-sequence');
      asiSequenceEl.textContent = '#' + (currentSequence + countDelta).toString();
      asiSequenceEl.dataset.before = (currentSequence + countDelta).toString();
    }
  });

  currentCollectionEl.scrollIntoView();

  return Number.parseInt(targetCollection.dataset.collectionId);
}

function displayImageFromApiCall(container, images, eventListenerAction, targetContainerId) {
  const loadMoreButton = container.querySelector('.media-load-more');

  images.forEach(image => {
    const existingImage = container.querySelector('img[data-media-id="' + image.id + '"]');
    if (existingImage) {
      addEventListenerAction(existingImage, image.id, eventListenerAction, targetContainerId);
      return;
    }

    const figureContainer = document.createElement('figure');
    figureContainer.className = 'image-container';

    const imageEl = new Image();
    imageEl.src = image.pathXSmall;
    imageEl.sizes = image.sizes;
    imageEl.srcset = image.srcset;
    const alt = image.caption ?? image.name;
    imageEl.alt = alt;
    imageEl.title = alt;
    imageEl.dataset.mediaId = image.id;
    imageEl.dataset.pathLarge = image.pathLarge;
    imageEl.className = 'media-item';
    imageEl.loading = 'lazy';
    addEventListenerAction(imageEl, image.id, eventListenerAction, targetContainerId);

    figureContainer.appendChild(imageEl);
    loadMoreButton.parentElement.insertBefore(figureContainer, loadMoreButton);
  });

  const mediaQueryQty = Number.parseInt(loadMoreButton.dataset.mediaQueryQty);

  if (images.length >= mediaQueryQty) {
    const loadMoreButton = container.parentElement.querySelector('.media-load-more');
    if (loadMoreButton) {
      loadMoreButton.classList.remove('null');
    }
  }
}

function deleteImage(e) {
  e.preventDefault();

  const mediaId = e.currentTarget.dataset.mediaId;
  const delRes = window.confirm(Global.get('feedbackDeleteImageConfirmation'));
  if (!delRes) {
    return;
  }

  Request.delete('/api/file/' + mediaId)
    .then(() => {
      document.querySelector(".media-item[data-media-id='" + mediaId + "']").parentElement.classList.add('null');
      document.querySelector('.modal-media').classList.add('null');
    });
}

function handleAlbumMediaDragEnter(ev) {
  ev.preventDefault();
}

function handleAlbumMediaDragLeave(ev) {
  ev.preventDefault();
  ev.currentTarget.classList.remove('media-item-grabbing-over');
}

function handleAlbumMediaDragOver(ev) {
  ev.preventDefault();
  ev.currentTarget.classList.add('media-item-grabbing-over');
}

function handleAlbumMediaDragEnd(ev) {
  document.querySelectorAll('.item-draggable .media-item').forEach(id => {
    id.removeEventListener('dragenter', handleAlbumMediaDragEnter);
    id.removeEventListener('dragover', handleAlbumMediaDragOver);
  });

  ev.currentTarget.classList.remove('media-item-grabbing-over');
  ev.currentTarget.classList.remove('media-item-grabbing');
}

function handleAlbumMediaDragStart(ev) {
  const collectionContainer = ev.currentTarget.parentElement.parentElement.parentElement;

  collectionContainer.querySelectorAll('.item-draggable .media-item').forEach(id => {
    id.addEventListener('dragenter', handleAlbumMediaDragEnter);
    id.addEventListener('dragover', handleAlbumMediaDragOver);
  });

  ev.dataTransfer.setData("text/plain", ev.currentTarget.id);
  ev.dataTransfer.dropEffect = "move";
  ev.effectAllowed = "move";
  ev.currentTarget.classList.add('media-item-grabbing');
}

function handleAlbumMediaDrop(ev) {
  ev.preventDefault();

  const collectionContainer = ev.currentTarget.parentElement.parentElement.parentElement;
  const draggedId = ev.dataTransfer.getData("text/plain");
  const draggedEl = document.getElementById(draggedId);

  if (!draggedEl) {
    return;
  }

  ev.currentTarget.classList.remove('media-item-grabbing-over');
  draggedEl.classList.add('media-item-grabbing');

  if (draggedEl.id === ev.currentTarget.id) {
    return;
  }

  const loadingEl = document.createElement('div');
  loadingEl.className = 'drop-loading loader-spinner';
  draggedEl.parentElement.appendChild(loadingEl);

  const droppedSequence = Number.parseInt(ev.currentTarget.dataset.sequence);
  const draggedSequence = Number.parseInt(draggedEl.dataset.sequence);

  const targetContainer = ev.currentTarget.parentElement.parentElement;
  const sourceContainer = draggedEl.parentElement.parentElement;
  let countDelta = 0;
  if (droppedSequence < draggedSequence) {
    targetContainer.parentNode.insertBefore(sourceContainer, targetContainer);
    countDelta = 1;
  } else {
    targetContainer.parentNode.insertBefore(sourceContainer, targetContainer.nextSibling);
    countDelta = -1;
  }

  const collectionId = collectionContainer.dataset.collectionId;
  const data = {
    sequenceTo: droppedSequence,
    collectionMediaIdTo: Number.parseInt(ev.currentTarget.dataset.collectionMediaId),
    sequenceFrom: draggedSequence,
    collectionMediaIdFrom: Number.parseInt(draggedEl.dataset.collectionMediaId),
    countDelta: countDelta,
  };

  Request.put('/back/collection/' + collectionId + '/sequence', JSON.stringify(data))
    .then(() => {
      draggedEl.dataset.sequence = droppedSequence.toString();

      collectionContainer.querySelectorAll('.item-draggable .media-item').forEach(mi => {
        if (draggedEl.id === mi.id) {
          return;
        }

        const cSeq = Number.parseInt(mi.dataset.sequence);
        if ((cSeq >= droppedSequence && cSeq < draggedSequence) || (cSeq <= droppedSequence && cSeq > draggedSequence)) {
          mi.dataset.sequence = (cSeq + countDelta).toString();
        }
      });
    })
    .catch(error => Util.notifyError(error))
    .finally(() => {
      loadingEl.parentElement.removeChild(loadingEl);
      draggedEl.classList.remove('media-item-grabbing');
    });
}

document.querySelectorAll('#images-list .media-item').forEach(im => {
  im.mediaId = im.dataset.mediaId;
  im.addEventListener('click', displayNextImagePopup);
});

document.querySelectorAll('.image-next-action, .image-previous-action, .image-random-action').forEach(ina => {
  ina.addEventListener('click', (e) => {
    e.preventDefault();

    const img = document.querySelector('.modal-media .image-wrapper .image-main img');
    if (!img) {
      // The previous image is still loading
      return;
    }

    img.mediaId = img.dataset.mediaId;
    img.direction = ina.dataset.direction;
    img.next = true;
    img.addEventListener('click', displayNextImagePopup);
    img.click();
  });
});

document.querySelectorAll('.image-info-action').forEach(iia => {
  iia.addEventListener('click', (e) => {
    e.preventDefault();

    const imageInfoVisibleEl = document.querySelector('.modal-media .image-info-visible');
    if (imageInfoVisibleEl) {
      imageInfoVisibleEl.classList.remove('image-info-visible');
      return;
    }

    const imageInfoEl = document.querySelector('.modal-media .image-info');
    if (imageInfoEl) {
      imageInfoEl.classList.add('image-info-visible');
    }
  });
});

document.querySelectorAll('.image-info-close-button').forEach(iic => {
  iic.addEventListener('click', (e) => {
    e.preventDefault();
    const imageInfoEl = document.querySelector('.image-wrapper .image-info');
    if (imageInfoEl) {
      imageInfoEl.classList.remove('image-info-visible');
    }
  });
});

document.querySelectorAll('.article-save-js').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    function afterApiCall(response) {
      history.pushState("", document.title, response.articleBackofficePath);
      document.querySelector('input[name="articleId"]').value = response.articleId;

      document.querySelectorAll('.article-save-js').forEach(b => {
        b.value = Global.get('globalUpdate');
      });
      const articlePreviewEl = document.querySelector('.editor-article-preview');
      articlePreviewEl.parentElement.classList.remove('null');
      articlePreviewEl.querySelector('a').href = response.articleBackofficePathPreview;
      articlePreviewEl.parentElement.querySelector('.editor-article-path').innerHTML = response.articlePublicUrlHtml;
    }

    function getTitleContent() {
      const titleEl = document.querySelector('input[name="articleTitle"]');
      return titleEl.value.trim().length ? titleEl.value.trim() : null;
    }

    function getPublishOnDateIsoString() {
      const publishOnDateEl = document.querySelector('input[name="publishOnDate"]');
      const publishOnTimeEl = document.querySelector('input[name="publishOnTime"]');

      return publishOnDateEl.value && publishOnTimeEl.value ?
        new Date(publishOnDateEl.value + 'T' + publishOnTimeEl.value + ':00').toISOString()
        : null;
    }

    function getArticleTypeId() {
      const articleTypeIdEl = document.querySelector('input[name="articleTypeId"]');
      return articleTypeIdEl && articleTypeIdEl.value.length ? Number.parseInt(articleTypeIdEl.value)
        : null;
    }

    function getStatusId() {
      const status = document.querySelector('.article-status-dd-option[data-checked="1"]');
      return Number.parseInt(status.dataset.value);
    }

    function getLanguageIsoCode() {
      const language = document.querySelector('.article-lang-dd-option[data-checked="1"]');
      return language.dataset.value;
    }

    const mediaIds = Array.from(document.querySelectorAll('trix-editor img[data-media-id]'))
      .map(({dataset}) => Number.parseInt(dataset.mediaId));

    const articleContentHtml = document.querySelector('input[name="articleContentHtml"]').value.trim();
    const contentHtml = articleContentHtml.length ? articleContentHtml : null;

    if (!contentHtml) {
      Util.notifyError(new Error(Global.get('feedbackSaving')));
      return;
    }

    const payload = JSON.stringify({
      siteLanguageIsoCode: document.documentElement.lang ?? 'EN',
      articleLanguageIsoCode: getLanguageIsoCode(),
      title: getTitleContent(),
      contentHtml: contentHtml,
      typeId: getArticleTypeId(),
      statusId: getStatusId(),
      mainImageId: mediaIds[0] ?? null,
      mediaIds: mediaIds,
      sections: [],
      publishOn: getPublishOnDateIsoString(),
    });

    const articleIdEl = document.querySelector('input[name="articleId"]');
    const url = '/back/article';
    if (articleIdEl && articleIdEl.value) {
      Request.put(url + '/' + articleIdEl.value, payload, Global.get('globalUpdated'))
        .then((res) => afterApiCall(res));
    } else {
      Request.post(url, payload, Global.get('globalSaved'))
        .then((res) => afterApiCall(res));
    }
  });
});

document.querySelectorAll('.image-delete').forEach(imgEl => {
  imgEl.addEventListener('click', deleteImage);
});

document.querySelectorAll('input#images').forEach(im => {
  im.addEventListener('change', e => {
    e.preventDefault();

    const container = document.querySelector('#images-list');

    Uploader.uploadMediaAsync(
      im.files,
      container,
      (response) => {
        if (response && response.file.id) {
          const newMediaEl = container.querySelector('.media-item[data-media-id="' + response.file.id + '"]');
          newMediaEl.addEventListener('click', displayNextImagePopup);
          newMediaEl.mediaId = response.file.id;
        }
      },
    )
      .catch(error => Util.notifyError(error));
  });
});

document.querySelectorAll('input#media').forEach(im => {
  im.addEventListener('change', e => {
    e.preventDefault();

    const container = document.querySelector('#media-container');

    Uploader.uploadMediaAsync(
      im.files,
      container,
      (response) => {
        if (response && response.file.id) {
          const newFileContainer = container.querySelector('.media-item[data-media-id="' + response.file.id + '"]');
          newFileContainer.mediaId = response.file.id;
        }
      },
    )
      .catch(error => Util.notifyError(error));
  });
});

document.querySelectorAll('form#form-user-creation').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const userIdEl = document.querySelector('input#userId');
    const nameEl = document.querySelector('input#name');
    const emailEl = document.querySelector('input#email');
    const bioEl = document.querySelector('textarea#bio');
    const languageIsoCodeEl = document.querySelector('select#languageIsoCode');
    const timezoneEl = document.querySelector('select#timezone');

    const userId = userIdEl && userIdEl.value ? Number.parseInt(userIdEl.value) : null;

    const payload = JSON.stringify({
      name: nameEl.value ?? null,
      email: emailEl.value ?? null,
      bio: bioEl.value.length ? bioEl.value : null,
      languageIsoCode: languageIsoCodeEl.value ?? null,
      timezone: timezoneEl.value ?? null,
    });

    if (userId) {
      Request.put('/back/user/' + userId, payload)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        })
        .catch(error => Util.notifyError(error));
    } else {
      Request.post('/back/user', payload)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        })
        .catch(error => Util.notifyError(error));
    }
  });
});

document.querySelectorAll('.filter-refresh').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = window.location.origin + window.location.pathname;
  });
});

document.querySelectorAll('.filter-article-refresh').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    const articleTypeId = document.querySelector('select[name="articleType"]').value;
    window.location.href = window.location.origin + window.location.pathname + '?atId=' + articleTypeId;
  });
});

document.querySelectorAll('.modal-media-close').forEach(mm => {
  mm.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelector('.modal-media').classList.add('null');
  });
});

document.addEventListener('keydown', e => {
  if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
    return;
  }

  if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
    document.querySelectorAll('.modal-media').forEach(m => {
      if (!m.classList.contains('null')) {
        const button = m.querySelector('.image-next-action');
        if (button && !button.classList.contains('hidden')) {
          e.preventDefault();
          button.click();
        }
      }
    });
  } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
    document.querySelectorAll('.modal-media').forEach(m => {
      if (!m.classList.contains('null')) {
        const button = m.querySelector('.image-previous-action');
        if (button && !button.classList.contains('hidden')) {
          e.preventDefault();
          button.click();
        }
      }
    });
  } else if (e.key === 'Escape') {
    document.querySelectorAll('.modal-media').forEach(m => {
      if (!m.classList.contains('null')) {
        m.querySelectorAll('.modal-media-close').forEach(b => b.click());
      }
    });
  } else if (e.key.toLowerCase() === 'r') {
    document.querySelectorAll('.modal-media').forEach(m => {
      const button = m.querySelector('.image-random-action');
      if (button) {
        e.preventDefault();
        button.click();
      }
    });
  } else if (e.key.toLowerCase() === 'i') {
    document.querySelectorAll('.modal-media').forEach(m => {
      const button = m.querySelector('.image-info-action');
      if (button) {
        e.preventDefault();
        button.click();
      }
    });
  }
});

document.querySelectorAll('.media-load-more-js').forEach(lm => {
  lm.addEventListener('click', e => {
    e.preventDefault();

    lm.disabled = true;
    const lastImageEl = document.querySelector('#images-list .image-container:last-of-type .media-item');
    const lastImageId = lastImageEl ? Number.parseInt(lastImageEl.dataset.mediaId) : null;
    const qty = Number.parseInt(lm.dataset.mediaQueryQty);
    const typeId = lm.dataset.typeId ? Number.parseInt(lm.dataset.typeId) : '';
    const direction = lm.dataset.direction ?? '';
    const eventListenerAction = lm.dataset.eventListenerAction;
    const targetContainerId = lm.dataset.targetContainerId ?? null;
    const loader = Util.buildImageLoadingElement('loader-container-100');
    lm.parentElement.appendChild(loader);
    lm.classList.add('null');

    Request.get('/api/file/' + lastImageId + '?direction=' + direction + '&typeId=' + typeId + '&qty=' + qty)
      .then(response => {
        const container = document.querySelector('#images-list');

        displayImageFromApiCall(container, response.files, eventListenerAction, targetContainerId);
        updateMediaCache(response.files, direction);

        lm.parentElement.removeChild(loader);
        lm.classList.remove('null');
        lm.disabled = false;

        if (response.files.length < qty) {
          lm.classList.add('null');
        }
      })
      .catch(error => Util.notifyError(error));
  });
});

document.querySelectorAll('.select-media-action').forEach(am => {
  am.addEventListener('click', handleGenericSelectMainMediaClick);
});

document.querySelectorAll('input[name="select-media-action-upload"]').forEach(im => {
  im.addEventListener('change', e => {
    e.preventDefault();

    const container = document.querySelector('#images-list');
    const eventListenerAction = im.dataset.eventListenerAction;
    const targetContainerId = im.dataset.targetContainerId;

    Uploader.uploadMediaAsync(
      im.files,
      container,
      (response) => {
        displayImageFromApiCall(container, [response.file], eventListenerAction, targetContainerId);
      },
    )
      .then()
      .catch(error => Util.notifyError(error));
  });
});

document.querySelectorAll('form#form-page-content').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    Util.displayFullPageLoadingModal();

    const container = document.querySelector('.content-language-wrapper');

    const contentTypeId = Number.parseInt(container.dataset.pageContentTypeId);
    const mainImageEl = f.querySelector('.media-item');
    const collectionEl = f.querySelector('.collection-item-media');
    const languageIsoCode = f.querySelector('input[name="languageIsoCode"]').value;

    const items = [];

    container.querySelectorAll('.content-language-item').forEach(li => {
      const contentId = li.querySelector('.page-content-id').value;
      const titleContent = li.querySelector('.page-content-title').value.trim();
      const subtitleContent = li.querySelector('.page-content-subtitle').value.trim();
      const contentHtml = li.querySelector('.page-content-content-html').value.trim();
      const actionUrl = li.querySelector('.page-content-action-url').value.trim();

      items.push({
        id: contentId.length ? Number.parseInt(contentId) : null,
        languageIsoCode: li.dataset.languageIsoCode,
        title: titleContent.length ? titleContent : null,
        subtitle: subtitleContent.length ? subtitleContent : null,
        contentHtml: contentHtml.length ? contentHtml : null,
        actionUrl: actionUrl.length ? actionUrl : null,
      });
    });

    const payload = {
      contentItems: items,
      collectionId: collectionEl && collectionEl.dataset.collectionId ? Number.parseInt(collectionEl.dataset.collectionId) : null,
      mainImageId: mainImageEl && mainImageEl.dataset.mediaId ? Number.parseInt(mainImageEl.dataset.mediaId) : null,
      languageIsoCode: languageIsoCode,
    };

    Request.put('/back/content/' + contentTypeId, JSON.stringify(payload))
      .then((response) => window.location = response.redirect)
      .catch(error => Util.notifyError(error))
      .finally(() => Util.hideFullPageLoadingModal());
  });
});

document.querySelectorAll('.page-content-flag-item').forEach(fi => {
  fi.addEventListener('click', e => {
    e.preventDefault();

    const isoCode = fi.dataset.languageIsoCode;

    document.querySelectorAll('.page-content-flag-item').forEach(cfi => {
      if (cfi.dataset.languageIsoCode === isoCode) {
        cfi.classList.add('flag-active');
      } else {
        cfi.classList.remove('flag-active');
      }
    });

    document.querySelectorAll('.content-language-item').forEach(cli => {
      if (cli.dataset.languageIsoCode === isoCode) {
        cli.classList.add('content-language-active');
      } else {
        cli.classList.remove('content-language-active');
      }
    });
  });
});

document.querySelectorAll('#form-album-edit').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const albumIdEl = f.querySelector('input[name="albumId"]');
    const albumId = albumIdEl && albumIdEl.value ? Number.parseInt(albumIdEl.value) : null;
    const mainMediaEl = f.querySelector('.main-image-container .media-item');
    const mainMediaId = mainMediaEl && mainMediaEl.dataset.mediaId ? Number.parseInt(mainMediaEl.dataset.mediaId)
      : null;

    if (!mainMediaId) {
      Util.notifyError(new Error('Media image is missing'));
      return;
    }

    const payload = JSON.stringify({
      languageIsoCode: document.documentElement.lang ? document.documentElement.lang.toUpperCase().trim() : 'EN',
      templateId: Number.parseInt(f.querySelector('select[name="albumTemplateId"]').value),
      mainMediaId: mainMediaId,
      titleHtml: f.querySelector('input[name="albumTitle"]').value.trim(),
      contentHtml: f.querySelector('input[name="albumContentHtml"]').value.trim(),
    });

    if (albumId) {
      Request.put('/back/album/' + albumId, payload)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        })
        .catch(error => Util.notifyError(error));
    } else {
      Request.post('/back/album', payload)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        })
        .catch(error => Util.notifyError(error));
    }
  });
});

document.querySelectorAll('.album-status-dd-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();

    const albumId = Number.parseInt(document.querySelector('input[name="albumId"]').value);
    const statusId = Number.parseInt(op.dataset.value);

    Request.put('/back/album/' + albumId + '/status/' + statusId, '')
      .then((response) => {
        document.querySelector('.form-public-link .value').innerHTML = response.publicLinkHtml;
      })
      .catch((error) => Util.notifyError(error));
  });
});

document.querySelectorAll('.album-add-collection-js').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();

    const container = document.querySelector('.collections-wrapper');
    const albumId = a.dataset.albumId;

    Util.displayFullPageLoadingModal();

    Request.post('/back/album/' + albumId + '/collection', '')
      .then(response => {
        container.insertAdjacentHTML('beforeend', response.html);
        const collectionContainer = container.querySelector('.collection-item[data-collection-id="' + response.newCollectionId + '"]');
        collectionContainer.querySelector('.collection-main-media-select-js').addEventListener('click', handleGenericSelectMainMediaClick);
        collectionContainer.querySelector('.collection-add-media-js').addEventListener('click', handleGenericSelectMainMediaClick);
        collectionContainer.querySelector('.collection-edit-js').addEventListener('click', editCollection);
        collectionContainer.querySelector('.collection-save-js').addEventListener('click', updateCollection);
        collectionContainer.querySelector('.collection-cancel-js').addEventListener('click', cancelCollectionEdit);
        collectionContainer.querySelector('.generic-media-delete-js').addEventListener('click', handleGenericMediaDeleteClick);
        collectionContainer.scrollIntoView({behavior: 'smooth', block: 'start'});
      })
      .catch(error => Util.notifyError(error))
      .finally(() => Util.hideFullPageLoadingModal());
  });
});

document.querySelectorAll('.collection-edit-js').forEach(bu => {
  bu.addEventListener('click', editCollection);
});

document.querySelectorAll('.collection-save-js').forEach(bu => {
  bu.addEventListener('click', updateCollection);
});

document.querySelectorAll('.collection-cancel-js').forEach(bu => {
  bu.addEventListener('click', cancelCollectionEdit);
});

document.querySelectorAll('.collection-main-media-js').forEach(bu => {
  bu.addEventListener('click', (e) => {
    e.preventDefault();

    const mediaId = bu.dataset.mediaId;
    const collectionId = bu.dataset.collectionId;

    if (mediaId && collectionId) {
      const existingModalContainer = document.querySelector('#images-list');
      const collectionMediaContainer = document.querySelector('#collection-item-media-' + collectionId);

      collectionMediaContainer.querySelectorAll('.collection-media-container .media-item').forEach(i => {
        addMediaToModalContainer(existingModalContainer, i.dataset.mediaId);
      });

      addMediaToModalContainer(existingModalContainer, mediaId);
    }

    handleGenericSelectMainMediaClick(e);
  });
});

document.querySelectorAll('.generic-media-delete-js').forEach(bu => {
  bu.targetContainerId = bu.dataset.targetContainerId;
  bu.mediaId = bu.dataset.mediaId;
  bu.addEventListener('click', handleGenericMediaDeleteClick);
});

document.querySelectorAll('.collection-media-delete-js').forEach(bu => {
  bu.targetContainerId = bu.dataset.targetContainerId;
  bu.mediaId = bu.dataset.mediaId;
  bu.addEventListener('click', collectionDeleteMedia);
});

document.querySelectorAll('.collection-main-media-delete-js').forEach(bu => {
  bu.targetContainerId = bu.dataset.targetContainerId;
  bu.mediaId = bu.dataset.mediaId;
  bu.addEventListener('click', collectionDeleteMainMedia);
});

document.querySelectorAll('.collection-media-caption-js').forEach(el => {
  el.mediaId = el.dataset.mediaId;
  el.addEventListener('click', collectionEditMediaCaption);
});

document.querySelectorAll('form#album-media-caption-edit-form-js').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const collectionMediaId = Number.parseInt(f.querySelector('input[name="collectionMediaId"]').value);
    const captionHtml = f.querySelector('.media-caption-html').textContent;

    const targetCaptionHtmlEl = document.querySelector(
      '.collection-media-caption-js[data-collection-media-id="' + collectionMediaId + '"]'
    );
    const captionHtmlBefore = targetCaptionHtmlEl.textContent;

    targetCaptionHtmlEl.textContent = captionHtml;
    document.querySelector('.album-media-caption-edit-modal-js').classList.add('null');

    const payload = JSON.stringify({
      captionHtml: captionHtml,
    });

    Request.put('/back/collection-media/' + collectionMediaId, payload)
      .catch(error => {
        targetCaptionHtmlEl.textContent = captionHtmlBefore;
        Util.notifyError(error);
      });
  });
});

document.querySelectorAll('.item-draggable .media-item').forEach(f => {
  f.addEventListener('dragstart', handleAlbumMediaDragStart);

  f.addEventListener('dragleave', handleAlbumMediaDragLeave);
  f.addEventListener('dragend', handleAlbumMediaDragEnd);

  f.addEventListener('drop', handleAlbumMediaDrop);
});

document.querySelectorAll('.generic-media-delete-js').forEach(b => {
  b.addEventListener('click', handleGenericMediaDeleteClick);
});

document.querySelectorAll('.email-content-js').forEach(ec => {
  ec.addEventListener('click', e => {
    e.preventDefault();

    const mailerId = ec.dataset.mailerId;

    Request.get('/back/mail/' + mailerId + '/html')
      .then(response => {
        const modal = document.querySelector('.modal-display-html-js');
        modal.querySelector('.html-container').innerHTML = response.html;
        modal.classList.remove('null');
      })
      .catch(error => Util.notifyError(error))
      .finally(() => Util.hideFullPageLoadingModal());
  });
});

document.querySelectorAll('.user-status-dd-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();

    const userId = op.closest('.dropdown-container').dataset.userId;
    const statusId = op.dataset.value;

    Request.put('/back/user/' + userId + '/status/' + statusId)
      .then(() => Util.notifyUser(Global.get('globalSaved')))
      .catch((error) => Util.notifyError(error));
  });
});

document.querySelectorAll('.user-role-dd-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();

    const userId = op.closest('.dropdown-container').dataset.userId;
    const roleId = op.dataset.value;

    Request.put('/back/user/' + userId + '/role/' + roleId)
      .then(() => {
        Util.notifyUser(Global.get('globalSaved'));
        window.location.reload();
      })
      .catch((error) => Util.notifyError(error));
  });
});

document.querySelectorAll('.filter-user-submit-js').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    const statusId = document.querySelector('select[name="statusId"]').value;
    const roleId = document.querySelector('select[name="roleId"]').value;

    let query = new URLSearchParams();

    if (statusId.length) {
      query.append('sId', statusId);
    }

    if (roleId.length) {
      query.append('rId', roleId);
    }

    if (!query.entries()) {
      document.querySelector('.filter-container').classList.remove('null');
      return;
    }

    const queryString = query.entries() ? '?' + query.toString() : '';

    window.location.href = window.location.origin + window.location.pathname + queryString;
  });
});

export {handleGenericMediaDeleteClick, handleGenericSelectMainMediaClick, addMediaToModalContainer};
