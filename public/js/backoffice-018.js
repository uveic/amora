import {Util} from './module/Util-009.js';
import {Request} from './module/Request-002.js';
import {Global} from "./module/localisation-004.js";
import {Uploader} from "./module/Uploader-010.js";

let globalTags = [];
const mediaCache = [];
const mediaCacheRight = [];
const mediaCacheLeft = [];

const selectMediaAction = (e) => {
  e.preventDefault();

  const button = e.currentTarget;

  const modal = document.querySelector('.select-media-modal');
  const loading = modal.querySelector('.select-media-modal-loading');
  const imagesContainer = modal.querySelector('#images-list');
  const loadMoreButton = modal.querySelector('.media-load-more-js');
  const uploadMediaButton = modal.querySelector('input[name="select-media-action-upload"]');
  const eventListenerAction = button.dataset.eventListenerAction;
  const targetContainerId = button.dataset.targetContainerId ?? null;
  uploadMediaButton.dataset.eventListenerAction = eventListenerAction;
  uploadMediaButton.dataset.targetContainerId = targetContainerId;
  loadMoreButton.dataset.eventListenerAction = eventListenerAction;
  loadMoreButton.dataset.targetContainerId = targetContainerId;
  modal.classList.remove('null');

  const qty = 50;
  const existingImages = imagesContainer.querySelectorAll('.image-item');
  existingImages.forEach(img => {
    addEventListenerAction(img, img.dataset.mediaId, eventListenerAction, targetContainerId);
  });

  if (existingImages.length >= qty) {
    return;
  }

  loading.classList.remove('null');
  const typeId = button.dataset.typeId ? Number.parseInt(button.dataset.typeId) : '';

  Request.get('/api/media?typeId=' + typeId + '&qty=' + qty)
    .then(response => {
      loading.classList.add('null');
      imagesContainer.classList.remove('null');
      displayImageFromApiCall(imagesContainer, response.files, eventListenerAction, targetContainerId);
    })
    .catch(error => {
      modal.classList.add('null');
      loading.classList.add('null');
      Util.notifyError(error);
    });
};

const addMediaToModalContainer = (existingModalContainer, mediaId) => {
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
  newImage.dataset.pathMedium = existingMedia.src;
  newImage.className = 'image-item media-dynamically-added';
  figureContainer.appendChild(newImage);

  if (existingModalContainer.firstChild) {
    existingModalContainer.insertBefore(figureContainer, existingModalContainer.firstChild);
  } else {
    existingModalContainer.appendChild(figureContainer);
  }
};

document.querySelectorAll('.article-save-button').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const afterApiCall = (articleId, articlePublicPath, articleBackofficePath) => {
      history.pushState("", document.title, articleBackofficePath);
      document.querySelector('input[name="articleId"]').value = articleId;

      document.querySelectorAll('#side-options').forEach(i => i.classList.add('null'));
      document.querySelectorAll('.article-save-button').forEach(b => {
        b.value = Global.get('globalUpdate');
      });
      document.querySelectorAll('.article-preview').forEach(b => {
        b.href = articlePublicPath;
        b.classList.remove('null');
      });
      document.querySelectorAll('.article-path-value').forEach(i => {
        i.textContent = articlePublicPath.trim().replace(/^\//,"");
      });
    };

    const getTitleContent = () => {
      const titleEl = document.querySelector('.articleTitle');
      if (titleEl && titleEl.textContent.trim().length) {
        return titleEl.textContent.trim();
      }

      return null;
    };

    const getTags = () => {
      let tags = [];
      document.querySelectorAll('#tags-selected > .result-selected')
        .forEach(t => {
          tags.push({
            id: t.dataset.tagId ? Number.parseInt(t.dataset.tagId) : null,
            name: t.dataset.tagName
          });
        });

      return tags;
    };

    const getContentHtmlAndSections = () => {
      const contentContainer = document.querySelector('.medium-editor-content');
      const imageIds = [];

      contentContainer.childNodes.forEach(node => {
        let currentNode = node;
        if (currentNode.nodeName === 'DIV') {
          const newParagraph = document.createElement('p');
          newParagraph.innerHTML = node.innerHTML;
          contentContainer.insertBefore(newParagraph, node);
          contentContainer.removeChild(node);
          currentNode = newParagraph;
        }

        if (currentNode.nodeName === 'IMG' && currentNode.dataset.mediaId) {
          imageIds.push(Number.parseInt(currentNode.dataset.mediaId));
        }

        if (currentNode.nodeName === '#text') {
          if (currentNode.textContent.trim().length) {
            const newParagraph = document.createElement('p');
            newParagraph.textContent = currentNode.textContent;
            contentContainer.insertBefore(newParagraph, node);
          }

          contentContainer.removeChild(node);
        }
      });

      const firstImageElement = contentContainer.querySelector('.article-image');
      const firstImageId = firstImageElement && firstImageElement.dataset.mediaId
          ? Number.parseInt(firstImageElement.dataset.mediaId)
          : null;

      return {
        sections: [],
        contentHtml: contentContainer.innerHTML.trim().length ? contentContainer.innerHTML.trim() : null,
        mainImageId: firstImageId,
        mediaIds: imageIds,
      };
    };

    const getPublishOnDateIsoString = () => {
      const publishOnDateEl = document.querySelector('input[name="publishOnDate"]');
      const publishOnTimeEl = document.querySelector('input[name="publishOnTime"]');

      return publishOnDateEl.value && publishOnTimeEl.value
        ? new Date(publishOnDateEl.value + 'T' + publishOnTimeEl.value + ':00').toISOString()
        : null;
    };

    const getArticleTypeId = () => {
      const articleTypeIdEl = document.querySelector('input[name="articleTypeId"]');
      return articleTypeIdEl && articleTypeIdEl.value.length
        ? Number.parseInt(articleTypeIdEl.value)
        : null;
    }

    const getPath = () => {
      const pathEl = document.querySelector('div.article-path-value');
      return pathEl && pathEl.textContent.trim().length ? pathEl.textContent.trim() : null;
    }

    const getStatusId = () => {
      const status = document.querySelector('.article-status-dd-option[data-checked="1"]');
      return Number.parseInt(status.dataset.value);
    };

    const getLanguageIsoCode = () => {
      const language = document.querySelector('.article-lang-dd-option[data-checked="1"]');
      return language.dataset.value;
    };

    const articleTitle = getTitleContent();
    const articleLanguageIsoCode = getLanguageIsoCode();
    const content = getContentHtmlAndSections();

    if (!content.contentHtml) {
      Util.notifyError(new Error(Global.get('feedbackSaving')));
      return;
    }

    const payload = JSON.stringify({
      siteLanguageIsoCode: document.documentElement.lang ?? articleLanguageIsoCode,
      articleLanguageIsoCode: articleLanguageIsoCode,
      title: articleTitle,
      path: getPath(),
      contentHtml: content.contentHtml,
      typeId: getArticleTypeId(),
      statusId: getStatusId(),
      mainImageId: content.mainImageId,
      mediaIds: content.mediaIds,
      sections: content.sections,
      tags: getTags(),
      publishOn: getPublishOnDateIsoString(),
    });

    const articleIdEl = document.querySelector('input[name="articleId"]');
    const url = '/back/article';
    if (articleIdEl && articleIdEl.value) {
      Request.put(url + '/' + articleIdEl.value, payload, Global.get('globalUpdated'))
        .then((res) => afterApiCall(res.articleId, res.articlePublicPath, res.articleBackofficePath));
    } else {
      Request.post(url, payload, Global.get('globalSaved'))
        .then((res) => afterApiCall(res.articleId, res.articlePublicPath, res.articleBackofficePath));
    }
  });
});

const handleCopyLink = (ev, href) => {
  ev.preventDefault();

  if (!navigator.clipboard) {
    Util.notifyError(new Error(Global.get('feedbackCopyLinkError')));
    return;
  }

  navigator.clipboard.writeText(href).then(() => {
    Util.notifyUser(Global.get('feedbackCopyLinkSuccess'));
  })
    .catch(error => Util.notifyError(error, Global.get('feedbackCopyLinkError')));
};

const displayModalImage = (image) => {
  const modalContainer = document.querySelector('.modal-media');
  const content = modalContainer.querySelector('.image-wrapper');
  const modalClose = modalContainer.querySelector('.modal-close-button');
  const imageWrapper = modalContainer.querySelector('.image-wrapper');
  let imageContainer = modalContainer.querySelector('.image-main img');
  const imageInfoData = modalContainer.querySelector('.image-info-data');
  const imageInfoNext = modalContainer.querySelector('.image-next-wrapper');
  const loaderContainer = modalContainer.querySelector('.loader-media');

  if (!imageContainer) {
    imageContainer = new Image();
    modalContainer.querySelector('.image-main').appendChild(imageContainer);
  }

  if (!image) {
    document.querySelectorAll('.image-next-action').forEach(i => i.classList.add('hidden'));
    content.classList.remove('null');
    modalClose.classList.remove('null');
    imageWrapper.classList.remove('filter-opacity');
    imageInfoData.classList.remove('null');
    imageInfoNext.classList.remove('null');
    loaderContainer.classList.add('null');

    return;
  }

  const alt = image.caption ?? image.name;
  imageContainer.src = image.pathMedium;
  imageContainer.alt = alt;
  imageContainer.title = alt;
  imageContainer.dataset.mediaId = image.id;
  imageContainer.classList.remove('hidden');
  imageWrapper.classList.remove('filter-opacity');
  loaderContainer.classList.add('null');
  imageInfoData.classList.remove('hidden');
  imageInfoNext.classList.remove('hidden');

  // Hide/display image nav buttons
  const firstImageEl = document.querySelector('#images-list .image-item');
  const firstImageId = firstImageEl ? Number.parseInt(firstImageEl.dataset.mediaId) : null;

  firstImageId === image.id
    ? document.querySelectorAll('.image-previous-action').forEach(i => i.classList.add('hidden'))
    : document.querySelectorAll('.image-previous-action').forEach(i => i.classList.remove('hidden'));

  document.querySelectorAll('.image-next-action').forEach(i => i.classList.remove('hidden'));

  let takenAt = '';
  if (image.exif && image.exif.date) {
    takenAt += '<img src="/img/svg/calendar-white.svg" class="img-svg" alt="Taken on">'
    + Global.formatDate(new Date(image.exif.date), true, true, true, true, true);
  }

  let camera = '';
  if (image.exif && image.exif.cameraModel) {
    camera += '<img src="/img/svg/camera-white.svg" class="img-svg" alt="EXIF">'
      + image.exif.cameraModel;
  }

  let aperture = '';
  if (image.exif && (image.exif.exposureTime || image.exif.ISO)) {
    let exposure = '';
    exposure += image.exif.exposureTime ?? '';
    if (image.exif.ISO) {
      if (exposure.length) {
        exposure += ' - ';
      }
      exposure += 'ISO: ' + image.exif.ISO;
    }
    aperture += '<img src="/img/svg/aperture-white.svg" class="img-svg" alt="Exposure time & ISO">'
      + exposure;
  }

  let size = '';
  if (image.exif && image.exif.sizeBytes) {
    size += '<img src="/img/svg/hard-drives-white.svg" class="img-svg" alt="Size (Mb)">'
      + (image.exif.sizeBytes / 1000000).toFixed(3) + ' Mb';
  }

  let pixels = '';
  if (image.exif && image.exif.width) {
    pixels += '<img src="/img/svg/frame-corners-white.svg" class="img-svg" alt="Size (pixels)">'
      + image.exif.width + ' x ' + image.exif.height
      + '<a href="' + image.fullPathOriginal + '" target="_blank"><img src="/img/svg/arrow-square-out-white.svg" class="img-svg" alt="Open image"></a>';
  }

  modalContainer.querySelector('.image-number').textContent = '#' + image.id;
  modalContainer.querySelector('.image-caption').textContent = image.caption;
  modalContainer.querySelector('.image-meta').innerHTML =
    '<div><img src="/img/svg/upload-simple-white.svg" class="img-svg" alt="Upload">'
    + Global.formatDate(new Date(image.createdAt), true, true, true, true, true)
    + '</div>'
    + (image.userName ? '<div><img src="/img/svg/user-white.svg" class="img-svg" alt="User">' + image.userName + '</div>': '')
    + '<div class="image-path">'
    + '<img src="/img/svg/link-white.svg" class="img-svg" alt="Link">'
    + '<span class="ellipsis">' + image.pathMedium + '</span>'
    + '<a href="' + image.fullPathMedium + '" target="_blank"><img src="/img/svg/arrow-square-out-white.svg" class="img-svg" alt="Open image"></a>'
    + '<a href="' + image.fullPathMedium + '" class="copy-link"><img src="/img/svg/copy-simple-white.svg" class="img-svg" alt="Copy link"></a>'
    + '</div>'
    + (takenAt ? '<div>' + takenAt + '</div>' : '')
    + (camera ? '<div>' + camera + '</div>' : '')
    + (aperture ? '<div>' + aperture + '</div>' : '')
    + (pixels ? '<div>' + pixels + '</div>' : '')
    + (size ? '<div>' + size + '</div>' : '');

  modalContainer.querySelector('.image-meta .copy-link')
    .addEventListener('click', e => handleCopyLink(e, image.fullPathMedium));

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
      appearsInfo.innerHTML = '<img src="/img/svg/calendar-white.svg" class="img-svg m-r-025" alt="Calendar">'
        + Global.formatDate(new Date(ao.publishedOn), false, false, true, false, false);
      appearsLink.appendChild(appearsInfo);
      appearsOnContainer.appendChild(appearsLink);
    });
  } else {
    imageDeleteEl.classList.remove('null');
  }

  imageInfoData.classList.remove('null');
  imageInfoNext.classList.remove('null');

  content.classList.remove('null');
  modalClose.classList.remove('null');
};

const preloadMedia = (mediaId, direction = 'DESC') => {
  const mediaIdToPreload = direction === 'DESC'
    ? mediaCacheRight[mediaId]
    : mediaCacheLeft[mediaId];

  if (!mediaIdToPreload) {
    return;
  }

  const mediaObj = mediaCache[mediaIdToPreload];
  if (!mediaObj) {
    return;
  }

  const imgTemp = new Image();
  imgTemp.src = mediaObj.fullPathMedium;
};

const updateMediaCache = (medias, direction = null, previousId = null) => {
  medias.forEach(item => {
    if (!mediaCache[item.id]) {
      mediaCache[item.id] = item;
    }

    if (previousId) {
      if (direction === 'ASC') {
        mediaCacheLeft[previousId] = item.id;
        mediaCacheRight[item.id] = previousId;
      } else if (direction === 'DESC') {
        mediaCacheLeft[item.id] = previousId;
        mediaCacheRight[previousId] = item.id;
      }
    }

    previousId = item.id;
  });
};

const modalRetrieveMediaAndAddToCache = async (mediaId, direction) => {
  if (direction === 'DESC' || direction === 'ASC') {
    const fourthNextMediaId = getFourthNextMediaId(mediaId, direction);
    if (fourthNextMediaId) {
      return mediaCache[mediaId];
    }
  }

  const qty = direction === 'DESC' || direction === 'ASC' ? 20 : 1;
  const typeId = document.querySelector('.media-load-more-js').dataset.typeId;
  const apiUrl = '/api/file/' + mediaId + '?direction=' + direction + '&typeId=' + typeId + '&qty=' + qty;

  return Request.get(apiUrl)
    .then(response => {
        updateMediaCache(response.files, direction, mediaId);
        return response.files[0] ?? null;
    });
};

const getFourthNextMediaId = (mediaId, direction) => {
  const nextMediaId = direction === 'DESC'
    ? mediaCacheRight[mediaId]
    : mediaCacheLeft[mediaId];

  const secondNextMediaId = nextMediaId
    ? (direction === 'DESC' ? mediaCacheRight[nextMediaId] : mediaCacheLeft[nextMediaId])
    : null;

  const thirdNextMediaId = secondNextMediaId
    ? (direction === 'DESC' ? mediaCacheRight[secondNextMediaId] : mediaCacheLeft[secondNextMediaId])
    : null;

  return thirdNextMediaId
    ? (direction === 'DESC' ? mediaCacheRight[thirdNextMediaId] : mediaCacheLeft[thirdNextMediaId])
    : null;
}

const modalGetMedia = async (mediaId, direction) => {
  if (mediaCache[mediaId]) {
    return mediaCache[mediaId];
  }

  const typeId = document.querySelector('.media-load-more-js').dataset.typeId;

  return Request.get('/api/file/' + mediaId + '?direction=' + direction + '&typeId=' + typeId + '&qty=20')
    .then(response => {
      updateMediaCache(response.files, direction);
      return response.files[0] ?? null;
    });
};

const modalGetNextMedia = async (currentMediaId, direction) => {
  let nextMediaId = null;
  if (direction === 'DESC' && mediaCacheRight[currentMediaId]) {
    nextMediaId = mediaCacheRight[currentMediaId];
  } else if (direction === 'ASC' && mediaCacheLeft[currentMediaId]) {
    nextMediaId = mediaCacheLeft[currentMediaId];
  }

  if (nextMediaId && mediaCache[nextMediaId]) {
    return mediaCache[nextMediaId];
  }

  return modalRetrieveMediaAndAddToCache(currentMediaId, direction)
    .then(mediaObj => {
      const newMedia = new Image();
      newMedia.src = mediaObj.fullPathMedium;

      return new Promise((resolve, reject) => {
        newMedia.onload = () => resolve(mediaObj);
        newMedia.onerror = reject;
      });
    });
};

const displayNextImagePopup = (e) => {
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
        preloadMedia(mediaObj.id, direction);
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
  imageWrapper.querySelector('.image-next-wrapper').classList.add('hidden');

  modalGetMedia(mediaId, direction)
    .then(mediaObj => {
      displayModalImage(mediaObj);
      preloadMedia(mediaObj.id, direction);
    });
};

const insertImageInArticle = (e) => {
  const container = document.querySelector('.medium-editor-content');
  const mediaId = e.currentTarget.mediaId;
  const existingImage = document.querySelector('img[data-media-id="' + mediaId + '"]');

  const newImage = new Image();
  newImage.className = 'article-image';
  newImage.src = existingImage.dataset.pathMedium;
  newImage.dataset.mediaId = existingImage.dataset.mediaId;
  newImage.alt = existingImage.alt;
  newImage.title = existingImage.title;

  const imageCaption = document.createElement('p');
  imageCaption.className = 'article-image-caption';
  imageCaption.innerHTML = '<br>';

  container.appendChild(newImage);
  container.appendChild(imageCaption)

  const newParagraph = document.createElement('p');
  newParagraph.innerHTML = '<br>';
  container.appendChild(newParagraph);

  document.querySelector('.select-media-modal').classList.add('null');

  imageCaption.scrollIntoView({ behavior: 'smooth', block: 'start' });
  imageCaption.focus();
};

const articleSelectMainImage = (e) => {
  const imageContainer = document.querySelector('.article-main-image-container');
  const targetImg = imageContainer.querySelector('img.article-main-image');
  const mediaId = e.currentTarget.mediaId;
  const sourceImgEl = document.querySelector('img[data-media-id="' + mediaId + '"]');

  if (targetImg) {
    targetImg.src = sourceImgEl.src;
    targetImg.alt = sourceImgEl.alt;
    targetImg.title = sourceImgEl.title;
    targetImg.dataset.mediaId = sourceImgEl.dataset.mediaId;
    targetImg.classList.add('article-main-image');
  } else {
    sourceImgEl.classList.add('article-main-image');
    imageContainer.appendChild(sourceImgEl);
  }

  document.querySelector('.select-media-action span').textContent = Global.get('globalModify');
  document.querySelector('.select-media-modal').classList.add('null');
  document.querySelector('.article-main-image-wrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const albumSelectMainMedia = (e) => {
  const mediaId = e.currentTarget.mediaId;

  const imageContainer = document.querySelector('.main-image-container');
  const targetImg = imageContainer.querySelector('.main-image');
  const sourceImg = document.querySelector('img[data-media-id="' + mediaId + '"]');

  if (targetImg) {
    targetImg.src = sourceImg.dataset.pathMedium;
    targetImg.alt = sourceImg.alt;
    targetImg.title = sourceImg.title;
    targetImg.dataset.mediaId = sourceImg.dataset.mediaId;
    targetImg.className = 'main-image';
  } else {
    const newImage = new Image();
    newImage.src = sourceImg.dataset.pathMedium;
    newImage.alt = sourceImg.alt;
    newImage.title = sourceImg.title;
    newImage.dataset.mediaId = sourceImg.dataset.mediaId;
    newImage.className = 'main-image';
    imageContainer.appendChild(newImage);
  }

  e.currentTarget.textContent = Global.get('globalModify');
  document.querySelector('.select-media-modal').classList.add('null');
  imageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const albumSectionAddMedia = (e) => {
  const mediaId = e.currentTarget.mediaId;
  const targetContainerId = e.currentTarget.targetContainerId;

  const container = document.querySelector('#' + targetContainerId);
  const albumSectionId = container.dataset.albumSectionId;
  const actionButton = container.querySelector('.select-media-action');

  const loadingContainer = document.createElement('div');
  loadingContainer.className = 'album-media-loading';
  const loadingAnimation = Util.createLoadingAnimation();
  loadingContainer.appendChild(loadingAnimation);
  container.insertBefore(loadingContainer, actionButton);

  const payload = {
    titleHtml: null,
    contentHtml: null,
    mediaId: mediaId,
  };

  document.querySelector('.select-media-modal').classList.add('null');
  actionButton.scrollIntoView({ behavior: 'smooth', block: 'center' });

  Request.post('/back/album-section/' + albumSectionId + '/media', JSON.stringify(payload))
    .then(response => {
      if (response.html) {
        actionButton.insertAdjacentHTML('beforebegin', response.html);
        const countEl = container.parentElement.querySelector('.album-section-item-media-header .count');
        countEl.textContent = (Number.parseInt(countEl.textContent) + 1).toString();

        const deleteMediaEl = container.querySelector('.album-section-media-delete-js[data-media-id="' + mediaId + '"]');
        deleteMediaEl.targetContainerId = targetContainerId;
        deleteMediaEl.mediaId = mediaId;
        deleteMediaEl.addEventListener('click', albumSectionDeleteMedia);

        const editMediaEl = container.querySelector('.album-section-media-caption-js[data-media-id="' + mediaId + '"]');
        editMediaEl.mediaId = mediaId;
        editMediaEl.addEventListener('click', albumSectionEditMediaCaption);

        const newMediaEl = container.querySelector('.item-draggable:last-of-type .media-item');
        newMediaEl.addEventListener('dragstart', handleAlbumMediaDragStart);
        newMediaEl.addEventListener('dragleave', handleAlbumMediaDragLeave);
        newMediaEl.addEventListener('dragend', handleAlbumMediaDragEnd);
        newMediaEl.addEventListener('drop', handleAlbumMediaDrop);
      } else {
        Util.notifyUser('A imaxe xa fora engadida.');
      }
    })
    .catch(error => {
      container.removeChild(container.querySelector('img[data-media-id="' + mediaId + '"]'));
      Util.notifyError(error);
    }).finally(() => container.removeChild(loadingContainer));
};

const editAlbumSection = (e) => {
  e.preventDefault();

  const albumSectionId = e.currentTarget.dataset.albumSectionId;

  document.querySelectorAll('.album-section-item').forEach(s => {
    const otherAlbumSectionId = s.dataset.albumSectionId;
    if (albumSectionId !== otherAlbumSectionId) {
      makeAlbumSectionNonEditable(otherAlbumSectionId);
    }
  })

  const container = document.querySelector('.album-section-item[data-album-section-id="' + albumSectionId + '"]');
  const titleEl = container.querySelector('.section-title-html');
  const subtitleEl = container.querySelector('.section-subtitle-html');
  const contentEl = container.querySelector('.section-content-html');
  const sequenceEl = container.querySelector('.section-sequence');
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

  container.querySelectorAll('.section-label').forEach(sl => sl.classList.remove('null'));
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
  container.querySelector('.album-section-button-container-js').classList.remove('null');
  container.querySelector('.main-image-container').classList.remove('null');
  const mainMedia = container.querySelector('.main-image-container .album-section-main-media');
  if (mainMedia && !mainMedia.classList.contains('null')) {
    container.querySelector('.album-section-main-media-delete-js').classList.remove('null');
  } else {
    container.querySelector('.album-section-main-media-delete-js').classList.add('null');
  }

  e.currentTarget.classList.add('null');
  titleEl.focus();

  Util.createMediumEditor('section-content-html-' + albumSectionId);

  container.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const updateAlbumSection = (e) => {
  e.preventDefault();

  const albumSectionId = e.currentTarget.dataset.albumSectionId;

  makeAlbumSectionNonEditable(albumSectionId);

  const container = document.querySelector('.album-section-item[data-album-section-id="' + albumSectionId + '"]');

  const titleHtmlEl = container.querySelector('.section-title-html');
  const titleHtml = titleHtmlEl.textContent.trim();
  titleHtmlEl.dataset.before = titleHtml;

  const subtitleHtmlEl = container.querySelector('.section-subtitle-html');
  const subtitleHtml = subtitleHtmlEl.textContent.trim();
  subtitleHtmlEl.dataset.before = subtitleHtml;

  const contentHtmlEl = container.querySelector('.section-content-html');
  const contentHtmlBeforeEl = container.querySelector('.section-content-html-before');
  const contentHtml = Util.getAndCleanHtmlFromElement(contentHtmlEl);
  contentHtmlBeforeEl.innerHTML = contentHtml ?? '';

  const mainMedia = container.querySelector('img.album-section-main-media');
  const mainMediaId = mainMedia && !mainMedia.classList.contains('null')
    ? Number.parseInt(mainMedia.dataset.mediaId)
    : null;
  container.querySelector('.main-image-container').dataset.before = mainMediaId ?? '';

  const sequenceEl = container.querySelector('.section-sequence');
  const sequenceRaw = sequenceEl.textContent.trim().replace('#', '');
  const sequence = Number.isNaN(sequenceRaw)
    ? Number.parseInt(sequenceEl.dataset.before)
    : Number.parseInt(sequenceEl.textContent.trim().replace('#', ''));

  const targetAlbumSectionId = updateAlbumSectionSequences(
    Number.parseInt(sequenceEl.dataset.before),
    sequence,
  );

  const payload = {
    titleHtml: titleHtml === '-' ? null : titleHtml,
    subtitleHtml: subtitleHtml === '-' ? null : subtitleHtml,
    contentHtml: contentHtml,
    mainMediaId: mainMediaId,
    newSequence: sequence,
    albumSectionIdSequenceTo: targetAlbumSectionId,
  };

  Request.put('/back/album-section/' + albumSectionId, JSON.stringify(payload))
    .then(() => {
      if (mainMedia && mainMedia.classList.contains('null')) {
        mainMedia.parentElement.removeChild(mainMedia);
      }
    })
    .catch(error => {
      Util.notifyError(error);
    });

  container.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const makeAlbumSectionNonEditable = (albumSectionId) => {
  const container = document.querySelector('.album-section-item[data-album-section-id="' + albumSectionId + '"]');
  const titleEl = container.querySelector('.section-title-html');
  const subtitleEl = container.querySelector('.section-subtitle-html');
  const contentEl = container.querySelector('.section-content-html');
  const mediaButtonsContainer = container.querySelector('.main-image-button-container');
  const editButton = container.querySelector('.album-section-edit-js');
  const sequenceEl = container.querySelector('.section-sequence');

  if (titleEl.textContent.trim() === '') {
    titleEl.textContent = '-';
  }

  if (subtitleEl.textContent.trim() === '') {
    subtitleEl.textContent = '-';
  }

  if (contentEl.textContent.trim() === '') {
    contentEl.textContent = '-';
  }

  container.querySelectorAll('.section-label').forEach(sl => sl.classList.add('null'));
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
  container.querySelector('.album-section-button-container-js').classList.add('null');
};

const cancelAlbumSectionEdit = (e) => {
  e.preventDefault();

  const albumSectionId = e.currentTarget.dataset.albumSectionId;

  const container = document.querySelector('.album-section-item[data-album-section-id="' + albumSectionId + '"]');
  const titleHtmlEl = container.querySelector('.section-title-html');
  titleHtmlEl.textContent = titleHtmlEl.dataset.before;
  const subtitleHtmlEl = container.querySelector('.section-subtitle-html');
  subtitleHtmlEl.textContent = subtitleHtmlEl.dataset.before;
  const contentHtmlEl = container.querySelector('.section-content-html');
  const contentHtmlBeforeEl = container.querySelector('.section-content-html-before');
  contentHtmlEl.innerHTML = contentHtmlBeforeEl.innerHTML;
  container.querySelector('.main-image-container').classList.remove('null');
  container.querySelector('.album-section-main-media-delete-js').classList.remove('null');

  const mainMedia = container.querySelector('.main-image-container .album-section-main-media');
  if (mainMedia) {
    if (mainMedia.parentElement.dataset.before
      && mainMedia.parentElement.dataset.before !== mainMedia.dataset.mediaId
    ) {
      const originalMedia = document.querySelector('img[data-media-id="' + mainMedia.parentElement.dataset.before + '"]');
      if (originalMedia) {
        mainMedia.src = originalMedia.src;
        mainMedia.dataset.mediaId = originalMedia.dataset.mediaId;
        mainMedia.alt = originalMedia.alt;
        mainMedia.title = originalMedia.title;
      }
    }

    mainMedia.parentElement.dataset.before
      ? mainMedia.classList.remove('null')
      : mainMedia.classList.add('null');

    mainMedia.classList.contains('null')
      ? container.querySelector('.album-section-main-media-js span').textContent = Global.get('globalSelectImage')
      : container.querySelector('.album-section-main-media-js span').textContent = Global.get('globalModify');
  }

  const sequenceEl = container.querySelector('.section-sequence');
  sequenceEl.textContent = '#' + sequenceEl.dataset.before;

  makeAlbumSectionNonEditable(albumSectionId);
  container.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const albumSectionSelectMainMedia = (e) => {
  e.preventDefault();

  const targetContainerId = e.currentTarget.targetContainerId;
  const mediaId = e.currentTarget.mediaId;

  const container = document.querySelector('#' + targetContainerId);
  const sourceImg = document.querySelector('img[data-media-id="' + mediaId + '"]');
  const targetImg = container.querySelector('.album-section-main-media');

  if (targetImg) {
    targetImg.src = sourceImg.src;
    targetImg.alt = sourceImg.alt;
    targetImg.title = sourceImg.title;
    targetImg.dataset.mediaId = sourceImg.dataset.mediaId;
    targetImg.className = 'album-section-main-media';
  } else {
    const newImage = new Image();
    newImage.src = sourceImg.src;
    newImage.alt = sourceImg.alt;
    newImage.title = sourceImg.title;
    newImage.dataset.mediaId = sourceImg.dataset.mediaId;
    newImage.className = 'album-section-main-media';
    container.insertBefore(newImage, container.firstChild);
  }

  container.querySelector('.album-section-main-media-js span').textContent = Global.get('globalModify');
  container.querySelector('.album-section-main-media-delete-js').classList.remove('null');
  document.querySelector('.select-media-modal').classList.add('null');
};

const albumSectionDeleteMainMedia = (e) => {
  e.preventDefault();

  const delRes = confirm(Global.get('feedbackDeleteGeneric'));
  if (!delRes) {
    return;
  }

  const targetContainerId = e.currentTarget.targetContainerId;
  const container = document.querySelector('#' + targetContainerId);

  container.querySelector('.album-section-main-media').classList.add('null');
  container.querySelector('.album-section-main-media-delete-js').classList.add('null');
  container.querySelector('.album-section-main-media-js span').textContent = Global.get('globalSelectImage');
};

const albumSectionDeleteMedia = (e) => {
  e.preventDefault();

  const delRes = confirm(Global.get('feedbackDeleteGeneric'));
  if (!delRes) {
    return;
  }

  const mediaId = e.currentTarget.mediaId;
  const albumSectionMediaId = e.currentTarget.dataset.albumSectionMediaId;
  const targetContainerId = e.currentTarget.targetContainerId;

  const container = document.querySelector('#' + targetContainerId);
  const targetMediaContainer = container.querySelector('img[data-media-id="' + mediaId + '"]');

  targetMediaContainer.parentElement.parentElement.classList.add('null');

  const countEl = container.parentElement.querySelector('.album-section-item-media-header .count');
  countEl.textContent = (Number.parseInt(countEl.textContent) - 1).toString();

  const deletedMediaSequence= Number.parseInt(targetMediaContainer.dataset.sequence);
  container.querySelectorAll('.media-item').forEach(mi => {
    const currentSequence = Number.parseInt(mi.dataset.sequence);
    if (currentSequence > deletedMediaSequence) {
      mi.dataset.sequence = (currentSequence - 1).toString();
    }
  });

  Request.delete('/back/album-section-media/' + albumSectionMediaId)
    .catch(error => {
      targetMediaContainer.parentElement.parentElement.classList.remove('null');
      Util.notifyError(error);

      countEl.textContent = (Number.parseInt(countEl.textContent) + 1).toString();

      container.querySelectorAll('.media-item').forEach(mi => {
        const currentSequence = Number.parseInt(mi.dataset.sequence);
        const currentMediaId = mi.dataset.mediaId;
        if (currentSequence >= deletedMediaSequence && mediaId !== currentMediaId) {
          mi.dataset.sequence = (currentSequence + 1).toString();
        }
      });
    });
};

const albumSectionEditMediaCaption = (e) => {
  e.preventDefault();

  const albumSectionMediaId = e.currentTarget.dataset.albumSectionMediaId;
  const albumSectionId = e.currentTarget.dataset.albumSectionId;
  const mediaId = e.currentTarget.mediaId;
  const modal = document.querySelector('.album-media-caption-edit-modal-js');
  const mediaContainer = modal.querySelector('.album-media-edit-container');
  mediaContainer.querySelectorAll('img').forEach(i => mediaContainer.removeChild(i));
  modal.querySelector('input[name="albumSectionMediaId"]').value = albumSectionMediaId;
  const existingMedia = e.currentTarget.parentElement.parentElement.querySelector('img[data-media-id="' + mediaId + '"]');
  const sectionContentContainer = document.querySelector(
    '.album-section-item[data-album-section-id="' + albumSectionId + '"]'
  );
  const titleText = sectionContentContainer.querySelector('.section-title-html').textContent;
  const subtitleText = sectionContentContainer.querySelector('.section-sequence').textContent
    + (titleText === '-' ? '' : (' ' + titleText));

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
};

const updateAlbumSectionSequences = (sourceSequence, targetSequence) => {
  if (sourceSequence === targetSequence) {
    return;
  }

  const container = document.querySelector('.album-sections-wrapper');
  const sourceSection = container.querySelector('.album-section-item[data-sequence="' + sourceSequence + '"]');
  let targetSection = container.querySelector('.album-section-item[data-sequence="' + targetSequence + '"]');
  if (!targetSection) {
    let closestSequence = null;
    const sequences = [];
    container.querySelectorAll('.album-section-item').forEach(asi => sequences.push(Number.parseInt(asi.dataset.sequence)));

    for (let i = 0; i < sequences.length; i++) {
      closestSequence = sequences[i];
      if (sequences[i] >= closestSequence) {
        break;
      }
    }

    targetSection = closestSequence
      ? container.querySelector('.album-section-item[data-sequence="' + closestSequence + '"]')
      : container.querySelector('.album-section-item:last-of-type');

    targetSequence = Number.parseInt(targetSection.dataset.sequence);
  }

  const currentSectionEl = sourceSection.querySelector('.section-sequence');
  currentSectionEl.textContent = '#' + targetSequence.toString();
  currentSectionEl.dataset.before = targetSequence.toString();
  sourceSection.dataset.sequence = targetSequence.toString();

  let countDelta = 0;
  if (targetSequence < sourceSequence) {
    container.insertBefore(sourceSection, targetSection);
    countDelta = 1;
  } else {
    targetSection.nextSibling
      ? container.insertBefore(sourceSection, targetSection.nextSibling)
      : container.insertAdjacentElement('beforeend', sourceSection);
    countDelta = -1;
  }

  container.querySelectorAll('.album-section-item').forEach(asi => {
    if (asi.dataset.albumSectionId === sourceSection.dataset.albumSectionId) {
      return;
    }

    const currentSequence = Number.parseInt(asi.dataset.sequence);
    if (
      (currentSequence >= targetSequence && currentSequence < sourceSequence)
      || (currentSequence <= targetSequence && currentSequence > sourceSequence)
    ) {
      asi.dataset.sequence = (currentSequence + countDelta).toString();
      const asiSequenceEl = asi.querySelector('.section-sequence');
      asiSequenceEl.textContent = '#' + (currentSequence + countDelta).toString();
      asiSequenceEl.dataset.before = (currentSequence + countDelta).toString();
    }
  });

  currentSectionEl.scrollIntoView();

  return Number.parseInt(targetSection.dataset.albumSectionId);
}

const addEventListenerAction = (image, mediaId, eventListenerAction, targetContainerId) => {
  if (eventListenerAction === 'displayNextImagePopup') {
    image.addEventListener('click', displayNextImagePopup);
  } else if (eventListenerAction === 'insertImageInArticle') {
    image.addEventListener('click', insertImageInArticle);
  } else if (eventListenerAction === 'articleSelectMainImage') {
    image.addEventListener('click', articleSelectMainImage);
  } else if (eventListenerAction === 'albumSelectMainMedia') {
    image.addEventListener('click', albumSelectMainMedia);
  } else if (eventListenerAction === 'albumSectionAddMedia') {
    image.addEventListener('click', albumSectionAddMedia);
  } else if (eventListenerAction === 'albumSectionSelectMainMedia') {
    image.addEventListener('click', albumSectionSelectMainMedia);
  }

  image.targetContainerId = targetContainerId;
  image.mediaId = mediaId;
};

const displayImageFromApiCall = (container, images, eventListenerAction, targetContainerId = null) => {
  images.forEach(image => {
    const existingImage = container.querySelector('img[data-media-id="' + image.id + '"]');
    if (existingImage) {
      addEventListenerAction(existingImage, image.id, eventListenerAction, targetContainerId);
      return;
    }

    const figureContainer = document.createElement('figure');
    figureContainer.className = 'image-container';

    const imageEl = new Image();
    imageEl.src = image.pathSmall;
    const alt = image.caption ?? image.name;
    imageEl.alt = alt;
    imageEl.title = alt;
    imageEl.dataset.mediaId = image.id;
    imageEl.dataset.pathMedium = image.pathMedium;
    imageEl.className = 'image-item';
    imageEl.loading = 'lazy';
    addEventListenerAction(imageEl, image.id, eventListenerAction, targetContainerId);

    figureContainer.appendChild(imageEl);
    container.appendChild(figureContainer);
  });
};

document.querySelectorAll('.image-item').forEach(im => {
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
    img.direction = ina.dataset.direction
    img.next = true;
    img.addEventListener('click', displayNextImagePopup);
    img.click();
  });
});

const deleteImage = async function (e, mediaId) {
  e.preventDefault();

  const delRes = confirm(Global.get('feedbackDeleteImageConfirmation'));
  if (!delRes) {
    return;
  }

  Request.delete('/api/file/' + mediaId)
    .then(() => {
      document.querySelector(".image-item[data-media-id='" + mediaId + "']").parentElement.classList.add('null');
      document.querySelector('.modal-media').classList.add('null');
    });
}

document.querySelectorAll('.image-delete').forEach(imgEl => {
  imgEl.addEventListener('click', e => {
    const mediaId = imgEl.dataset.mediaId;
    deleteImage(e, mediaId).then();
  });
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
          const newMediaEl = container.querySelector('.image-item[data-media-id="' + response.file.id + '"]');
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

    for (let i = 0; i < im.files.length; i++) {
      let file = im.files[i];

      let newMediaContainer = document.createElement('a');
      newMediaContainer.href = '#';
      newMediaContainer.className = 'media-item';
      newMediaContainer.target = '_blank';
      container.insertBefore(newMediaContainer, container.firstChild);

      Uploader.uploadFile(
        file,
        newMediaContainer,
        '',
        (response) => {
          if (response && response.file.id) {
            newMediaContainer.dataset.mediaId = response.file.id;
            newMediaContainer.href = response.file.path;

            const mediaId = document.createElement('span');
            mediaId.textContent = '#' + response.file.id;
            mediaId.className = 'media-id';
            const mediaName = document.createElement('span');
            mediaName.textContent = response.file.caption;
            mediaName.className = 'media-name';
            const mediaInfo = document.createElement('span');
            mediaInfo.className = 'media-info';
            mediaInfo.textContent = Global.get('globalUploadedOn') + ' '
              + Global.formatDate(new Date(response.file.createdAt), true, true, true, true, true)
              + ' ' + Global.get('globalBy') + ' ' + response.file.userName + '.';

            newMediaContainer.appendChild(mediaId);
            newMediaContainer.innerHTML += '<img src="/img/svg/file-pdf.svg" class="img-svg img-svg-40 m-r-05" alt="PDF">';
            newMediaContainer.appendChild(mediaName);
            newMediaContainer.appendChild(mediaInfo);
          }
        },
        () => container.removeChild(newMediaContainer),
      );
    }
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
    const roleIdEl = document.querySelector('select#roleId');
    const timezoneEl = document.querySelector('select#timezone');
    const userStatusEl = document.querySelector('select#userStatusId');

    const userId = userIdEl && userIdEl.value ? Number.parseInt(userIdEl.value) : null;

    const payload = JSON.stringify({
      name: nameEl.value ?? null,
      email: emailEl.value ?? null,
      bio: bioEl.value.length ? bioEl.value : null,
      languageIsoCode: languageIsoCodeEl.value ?? null,
      roleId: roleIdEl.value ? Number.parseInt(roleIdEl.value) : null,
      timezone: timezoneEl.value ?? null,
      userStatusId: Number.parseInt(userStatusEl.value),
    });

    if (userId) {
      Request.put('/back/user/' + userId, payload)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        });
    } else {
      Request.post('/back/user', payload)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        });
    }
  });
});

const handleDropdownOptionClick = (elementOption, dropDownIdentifier) => {
  const elementLabel = document.querySelector('#' + dropDownIdentifier + '-dd-label');
  const elementCheckbox = document.querySelector('#' + dropDownIdentifier + '-dd-checkbox');
  const optionClassName = dropDownIdentifier + '-dd-option';

  if (elementOption.dataset.value === '1') {
    elementLabel.classList.add('feedback-success');
    elementLabel.classList.remove('background-light-text-color');
  } else {
    elementLabel.classList.remove('feedback-success');
    elementLabel.classList.add('background-light-text-color');
  }

  elementLabel.querySelector('span').innerHTML = elementOption.innerHTML;
  elementCheckbox.checked = false;

  document.querySelectorAll('.' + optionClassName).forEach(o => {
    o.dataset.checked = o.dataset.value === elementOption.dataset.value
      ? '1'
      : '0';
  });
};

document.querySelectorAll('.article-status-dd-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();
    handleDropdownOptionClick(op, 'article-status');
  });
});

document.querySelectorAll('.user-status-dd-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();
    handleDropdownOptionClick(op, 'user-status');
  });
});

document.querySelectorAll('.article-lang-dd-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();
    handleDropdownOptionClick(op, 'article-lang');
  });
});

document.querySelectorAll('a.article-settings').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('input[name="tags"]').forEach(i => i.value = '');

    const pathContainer = document.querySelector('.article-edit-previous-path-container');
    const pathContent = pathContainer.querySelector('.article-edit-previous-path-content');
    pathContent.querySelector('img').classList.remove('null');
    pathContent.querySelectorAll('div').forEach(di => pathContent.removeChild(di));

    const sideNav = document.getElementById('side-options');
    sideNav.classList.remove('null');
    sideNav.classList.add('side-options-open');

    if (!globalTags.length) {
      const searchResultEl = document.querySelector('#search-results-tags');
      Request.get('/back/tag')
        .then(response => {
          globalTags = response.tags;
          globalTags.forEach(tag => {
            let newTag = document.createElement('span');
            newTag.className = 'result-item';
            newTag.dataset.tagId = tag.id;
            newTag.dataset.tagName = tag.name;
            newTag.textContent = tag.name;
            newTag.addEventListener('click', () => handleTagSearchResultClick(tag.id, tag.name, tag.name));
            searchResultEl.appendChild(newTag);
          });
        });
    }

    const articleId = document.querySelector('input[name="articleId"]').value;
    if (articleId) {
      Request.get('/back/article/' + articleId + '/previous-path')
        .then(response => {
          response.paths.forEach(p => {
            const newPathEl = document.createElement('div');
            newPathEl.dataset.pathId = p.id;
            newPathEl.innerHTML = '<span>' + p.path + '</span><span>'
              + Global.formatDate(new Date(p.createdAt), false) + '</span>';
            newPathEl.className = 'article-edit-previous-path';
            pathContainer.appendChild(newPathEl);
          });

          pathContainer.querySelector('img').classList.add('null');
        });
    } else {
      pathContainer.querySelector('img').classList.add('null');
    }
  });
});

document.querySelectorAll('a.close-button').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();

    const sideNav = document.getElementById('side-options');
    sideNav.classList.add('null');
    sideNav.classList.remove('side-options-open');
  });
});

const handleRemoveArticleTag = (event) => {
  event.target.parentNode.parentNode.removeChild(event.target.parentNode);

  const allTags = document.querySelectorAll('#tags-selected span');
  if (!allTags.length) {
    document.querySelector('#tags-selected').classList.add('null');
  }
};

const handleTagSearchResultClick = (tagId, tagName, tagInnerHtml, tagElement = null) => {
  const tags = document.querySelector('#tags-selected');
  tags.classList.remove('null');
  const allResults = document.querySelectorAll('.search-results > .result-item');

  const generateNewTagHtml = function(id, name, html) {
    let imgClose = new Image();
    imgClose.className = 'img-svg m-l-05';
    imgClose.title = Global.get('globalRemove');
    imgClose.alt = Global.get('globalRemove');
    imgClose.src = '/img/svg/x.svg';
    imgClose.addEventListener('click', (e) => handleRemoveArticleTag(e));

    let newTag = document.createElement('span');
    newTag.className = 'result-selected';
    newTag.dataset.tagId = id;
    newTag.dataset.tagName = name;
    newTag.innerHTML = html;
    newTag.appendChild(imgClose);
    tags.appendChild(newTag);
  }

  document.querySelector('#search-results-tags').classList.add('null');
  document.querySelector('input[name="tags"]').value = '';
  allResults.forEach(r => r.classList.remove('null'));
  tagId = Number.parseInt(tagId);

  if (tagId) {
    let existingTag = [];
    document.querySelectorAll('#tags-selected > .result-selected')
      .forEach(s => {
        if (tagId === Number.parseInt(s.dataset.tagId)) {
          s.classList.add('highlight-effect');
          setTimeout(() => s.classList.remove('highlight-effect'), 2000);
        }
        existingTag.push(Number.parseInt(s.dataset.tagId));
      });

    if (existingTag.includes(tagId)) {
      return;
    }

    generateNewTagHtml(tagId, tagName, tagInnerHtml);
    return;
  }

  Request.post('/back/tag', JSON.stringify({name: tagName}))
    .then(res => {
      if (tagElement) {
        tagElement.parentNode.removeChild(tagElement);
      }
      generateNewTagHtml(res.id, tagName, tagInnerHtml);
    });
};

document.querySelectorAll('input[name="tags"]').forEach(el => {
  const searchResultEl = document.querySelector('#search-results-tags');

  el.addEventListener('keyup', (e) => {
    e.preventDefault();

    searchResultEl.classList.remove('null');

    if (e.keyCode === 37 || e.keyCode === 39) { // left/right arrows
      return;
    }

    const allResults = document.querySelectorAll('.search-results > .result-item');

    let count = allResults.length;
    const inputText = e.target.value.trim();
    const cleanInput = Util.cleanString(inputText);
    allResults.forEach(r => {
      if (r.dataset.new) {
        r.parentNode.removeChild(r);
        count--;
        return;
      }

      if (Util.cleanString(r.textContent).includes(cleanInput)) {
        r.classList.remove('null');
      } else {
        count--;
        r.classList.add('null');
      }
    });

    if (!count) {
      let newTag = document.createElement('span');
      newTag.className = 'result-item';
      newTag.dataset.tagName = inputText;
      newTag.dataset.new = '1';
      newTag.innerHTML = '<span class="new">New</span>' + inputText;
      newTag.addEventListener('click', () => {
        handleTagSearchResultClick(null, inputText, newTag.innerHTML, newTag);
      });
      searchResultEl.appendChild(newTag);
    }
  });

  el.addEventListener('focus', () => searchResultEl.classList.remove('null'));
});

document.querySelectorAll('a.search-results-close').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelector('#search-results-tags').classList.add('null');
  });
});

document.querySelectorAll('.tag-result-selected-delete').forEach(i => {
  i.addEventListener('click', (e) => handleRemoveArticleTag(e));
});

document.querySelectorAll('.filter-close').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelector('.filter-container').classList.add('null');
  });
});

document.querySelectorAll('.filter-open').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelector('.filter-container').classList.remove('null');
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

document.querySelectorAll('.filter-article-button').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    const status = document.querySelector('select[name="articleStatus"]').value;
    const type = document.querySelector('select[name="articleType"]').value;
    const lang = document.querySelector('select[name="articleLanguageIsoCode"]').value;

    let query = new URLSearchParams();

    if (status.length) {
      query.append('status', status);
    }

    if (type.length) {
      query.append('atId', type);
    }

    if (lang.length) {
      query.append('lang', lang);
    }

    if (!query.entries()) {
      document.querySelector('.filter-container').classList.remove('null');
      return;
    }

    const queryString = query.entries() ? '?' + query.toString() : '';

    window.location.href = window.location.origin + window.location.pathname + queryString;
  });
});

document.addEventListener('keydown', e => {
  if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
    return;
  }

  if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
    document.querySelectorAll('.modal-wrapper').forEach(m => {
      if (!m.classList.contains('null')) {
        const button = m.querySelector('a.image-next-action');
        if (button && !button.classList.contains('hidden')) {
          e.preventDefault();
          button.click();
        }
      }
    });
  } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
    document.querySelectorAll('.modal-wrapper').forEach(m => {
      if (!m.classList.contains('null')) {
        const button = m.querySelector('a.image-previous-action');
        if (button && !button.classList.contains('hidden')) {
          e.preventDefault();
          button.click();
        }
      }
    });
  } else if (e.key === 'Escape') {
    document.querySelectorAll('.modal-wrapper').forEach(m => {
      if (!m.classList.contains('null')) {
        m.querySelector('a.modal-close-button').click();
      }
    });
  } else if (e.key.toLowerCase() === 'r') {
    document.querySelectorAll('.modal-wrapper').forEach(m => {
      const button = m.querySelector('a.image-random-action');
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
    const lastImageEl = document.querySelector('#images-list .image-container:last-child .image-item');
    const lastImageId = lastImageEl ? Number.parseInt(lastImageEl.dataset.mediaId) : null;
    const qty = 50;
    const typeId = lm.dataset.typeId ? Number.parseInt(lm.dataset.typeId) : '';
    const dir = lm.dataset.direccion ?? '';
    const eventListenerAction = lm.dataset.eventListenerAction;
    const targetContainerId = lm.dataset.targetContainerId ?? null;

    Request.get('/api/file/' + lastImageId + '?direction=' + dir + '&typeId=' + typeId + '&qty=' + qty)
      .then(response => {
        const container = document.querySelector('#images-list');

        displayImageFromApiCall(container, response.files, eventListenerAction, targetContainerId);

        lm.disabled = false;

        if (response.files.length < qty) {
          lm.classList.add('null');
        }
      });
  });
});

document.querySelectorAll('.select-media-action').forEach(am => {
  am.addEventListener('click', selectMediaAction);
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

document.querySelectorAll('.editor-title').forEach(t => {
  t.addEventListener('focus', () => {
    const content = t.textContent.trim();

    t.classList.remove('editor-placeholder');

    if (content === Global.get('editorTitlePlaceholder')) {
      t.innerHTML = '';
    }
  });

  t.addEventListener('blur', () => {
    const content = t.textContent.trim();

    if (!content.length) {
      t.innerHTML = Global.get('editorTitlePlaceholder');
      t.classList.add('editor-placeholder');
    }
  });
});

document.querySelectorAll('.editor-subtitle').forEach(t => {
  t.addEventListener('focus', () => {
    const content = t.textContent.trim();

    t.classList.remove('editor-placeholder');

    if (content === Global.get('editorSubtitlePlaceholder')) {
      t.innerHTML = '';
    }
  });

  t.addEventListener('blur', () => {
    const content = t.textContent.trim();

    if (!content.length) {
      t.innerHTML = Global.get('editorSubtitlePlaceholder');
      t.classList.add('editor-placeholder');
    }
  });
});

if (document.querySelector('.medium-editor-content')) {
  Util.createMediumEditor('medium-editor-content');

  const container = document.querySelector('.medium-editor-content');
  if (container.textContent.trim().length && container.lastElementChild &&
      (container.lastElementChild.nodeName !== 'P'
        || (container.lastElementChild.nodeName === 'P' && container.lastElementChild.className !== '')
      )
    ) {
      const newParagraph = document.createElement('p');
      newParagraph.innerHTML = '<br>';
      container.appendChild(newParagraph);
    }
}

document.querySelectorAll('form#form-page-content').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const contentId = Number.parseInt(f.querySelector('input[name="contentId"]').value);
    const titleHtml = f.querySelector('.editor-title').innerHTML.trim();
    const subtitleHtml = f.querySelector('.editor-subtitle').innerHTML.trim();
    const contentHtml = f.querySelector('.editor-content').innerHTML.trim();
    const mainImageEl = f.querySelector('.article-main-image');

    const payload = JSON.stringify({
      titleHtml: titleHtml.length
        ? (titleHtml === Global.get('editorTitlePlaceholder') ? null : titleHtml)
        : null,
      subtitleHtml: subtitleHtml.length
        ? (subtitleHtml === Global.get('editorSubtitlePlaceholder') ? null : subtitleHtml)
        : null,
      contentHtml: contentHtml,
      mainImageId: mainImageEl && mainImageEl.dataset.mediaId
        ? Number.parseInt(mainImageEl.dataset.mediaId)
        : null,
    });

    Request.put('/back/content/' + contentId, payload)
      .then((response) => window.location = response.redirect);
  });
});

document.querySelectorAll('.modal-close-button').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();
    el.parentElement.parentElement.classList.add('null');
  });
});

document.querySelectorAll('.copy-link').forEach(a => {
  a.addEventListener('click', e => handleCopyLink(e, a.href));
});

document.querySelectorAll('.article-add-section-video').forEach(bu => {
  bu.addEventListener('click', (e) => {
    e.preventDefault();

    const videoUrl = window.prompt(Global.get('editorVideoUrlTitle'));
    if (videoUrl) {
      const ytVideoId = Util.getYoutubeVideoIdFromUrl(videoUrl);
      if (!ytVideoId) {
        return;
      }

      let videoWrapper = document.createElement('div');
      videoWrapper.className = 'section-video-youtube';
      let iframeElement = document.createElement('iframe');
      iframeElement.width = '560';
      iframeElement.height = '315';
      iframeElement.src = 'https://www.youtube-nocookie.com/embed/' + ytVideoId;
      iframeElement.frameBorder = '0';
      iframeElement.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframeElement.allowFullscreen = true;

      videoWrapper.appendChild(iframeElement);

      const container = document.querySelector('.medium-editor-content');
      container.appendChild(videoWrapper);
      const newParagraph = document.createElement('p');
      newParagraph.innerHTML = '<br>';
      container.appendChild(newParagraph);
    }
  });
});

document.querySelectorAll('.filter-album-button').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    const status = document.querySelector('select[name="albumStatus"]').value;
    const lang = document.querySelector('select[name="albumLanguageIsoCode"]').value;

    let query = new URLSearchParams();

    if (status.length) {
      query.append('status', status);
    }

    if (lang.length) {
      query.append('lang', lang);
    }

    if (!query.entries()) {
      document.querySelector('.filter-container').classList.remove('null');
      return;
    }

    const queryString = query.entries() ? '?' + query.toString() : '';

    window.location.href = window.location.origin + window.location.pathname + queryString;
  });
});

document.querySelectorAll('#form-album-edit').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const albumIdEl = f.querySelector('input[name="albumId"]');
    const albumId = albumIdEl && albumIdEl.value ? Number.parseInt(albumIdEl.value) : null;
    const mainMediaEl = f.querySelector('.main-image-container .main-image');
    const mainMediaId = mainMediaEl && mainMediaEl.dataset.mediaId
      ? Number.parseInt(mainMediaEl.dataset.mediaId)
      : null;

    if (!mainMediaId) {
      Util.notifyError(new Error('Media image is missing'));
      return;
    }

    const payload = JSON.stringify({
      languageIsoCode: document.documentElement.lang
        ? document.documentElement.lang.toUpperCase().trim()
        : 'EN',
      templateId: Number.parseInt(f.querySelector('select[name="albumTemplateId"]').value),
      mainMediaId: mainMediaId,
      titleHtml: f.querySelector('input[name="albumTitle"]').value.trim(),
      contentHtml: f.querySelector('.medium-editor-content').innerHTML.trim(),
    });

    if (albumId) {
      Request.put('/back/album/' + albumId, payload)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        });
    } else {
      Request.post('/back/album', payload)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        });
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
        handleDropdownOptionClick(op, 'album-status');

        document.querySelector('.form-public-link .value').innerHTML = response.publicLinkHtml;
      })
      .catch((error) => {
        document.querySelector('#event-status-dd-checkbox').checked = false;
        Util.notifyError(error);
      });
  });
});

document.querySelectorAll('.album-add-section-js').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();

    const container = document.querySelector('.album-sections-wrapper');
    const albumId = a.dataset.albumId;

    const loadingContainer = document.createElement('div');
    loadingContainer.className = 'album-section-loading';
    const loadingAnimation = Util.createLoadingAnimation();
    loadingContainer.appendChild(loadingAnimation);
    container.appendChild(loadingContainer);

    Request.post('/back/album/' + albumId + '/section', '')
      .then(response => {
        container.insertAdjacentHTML('beforeend', response.html);
        const sectionContainer = container.querySelector('.album-section-item[data-album-section-id="' + response.newSectionId + '"]');
        sectionContainer.querySelector('.select-media-action').addEventListener('click', selectMediaAction);
        sectionContainer.querySelector('.album-section-main-media-js').addEventListener('click', selectMediaAction);
        sectionContainer.querySelector('.album-section-edit-js').addEventListener('click', editAlbumSection);
        sectionContainer.querySelector('.album-section-save-js').addEventListener('click', updateAlbumSection);
        sectionContainer.querySelector('.album-section-cancel-js').addEventListener('click', cancelAlbumSectionEdit);
        sectionContainer.querySelector('.album-section-main-media-delete-js').addEventListener('click', albumSectionDeleteMainMedia);
        sectionContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
      })
      .catch(error => {
        Util.notifyError(error);
      }).finally(() => container.removeChild(loadingContainer));
  });
});

document.querySelectorAll('#album-add-section-form-js').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const albumId = Number.parseInt(f.querySelector('input[name="albumId"]').value);

    const payload = {
      mainMediaId: 1,
      titleHtml: f.querySelector('input[name="albumSectionTitle"]').value.trim(),
      contentHtml: f.querySelector('.medium-editor-content').innerHTML.trim(),
    };

    Request.post('/back/album/' + albumId + '/section', JSON.stringify(payload))
      .then(response => {
        const modal = document.querySelector('.album-add-section-modal-js');
        const sectionContainer = document.querySelector('.album-sections-wrapper');
        sectionContainer.innerHTML += response.html;
        modal.classList.add('null');
      });
  });
});

document.querySelectorAll('.album-section-edit-js').forEach(bu => {
  bu.addEventListener('click', editAlbumSection);
});

document.querySelectorAll('.album-section-save-js').forEach(bu => {
  bu.addEventListener('click', updateAlbumSection);
});

document.querySelectorAll('.album-section-cancel-js').forEach(bu => {
  bu.addEventListener('click', cancelAlbumSectionEdit);
});

document.querySelectorAll('.album-section-main-media-js').forEach(bu => {
  bu.addEventListener('click', (e) => {
    e.preventDefault();

    const mediaId = bu.dataset.mediaId;
    const sectionId = bu.dataset.sectionId;

    const existingModalContainer = document.querySelector('#images-list');
    const sectionMediaContainer = document.querySelector('#album-section-item-media-' + sectionId);

    sectionMediaContainer.querySelectorAll('.album-section-media-container .media-item').forEach(i => {
      addMediaToModalContainer(existingModalContainer, i.dataset.mediaId);
    });

    addMediaToModalContainer(existingModalContainer, mediaId);

    selectMediaAction(e);
  });
});

document.querySelectorAll('.album-section-main-media-delete-js').forEach(bu => {
  bu.targetContainerId = bu.dataset.targetContainerId;
  bu.mediaId = bu.dataset.mediaId;
  bu.addEventListener('click', albumSectionDeleteMainMedia);
});

document.querySelectorAll('.album-section-media-delete-js').forEach(bu => {
  bu.targetContainerId = bu.dataset.targetContainerId;
  bu.mediaId = bu.dataset.mediaId;
  bu.addEventListener('click', albumSectionDeleteMedia);
});

document.querySelectorAll('.album-section-media-caption-js').forEach(el => {
  el.mediaId = el.dataset.mediaId;
  el.addEventListener('click', albumSectionEditMediaCaption);
});

document.querySelectorAll('form#album-media-caption-edit-form-js').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const albumSectionMediaId = Number.parseInt(f.querySelector('input[name="albumSectionMediaId"]').value);
    const captionHtml = f.querySelector('.media-caption-html').textContent;

    const targetCaptionHtmlEl = document.querySelector(
      '.album-section-media-caption-js[data-album-section-media-id="' + albumSectionMediaId + '"]'
    );
    const captionHtmlBefore = targetCaptionHtmlEl.textContent;

    targetCaptionHtmlEl.textContent = captionHtml;
    document.querySelector('.album-media-caption-edit-modal-js').classList.add('null');

    const payload = JSON.stringify({
      captionHtml: captionHtml,
    });

    Request.put('/back/album-section-media/' + albumSectionMediaId, payload)
      .catch(error => {
        targetCaptionHtmlEl.textContent = captionHtmlBefore;
        Util.notifyError(error);
      });
  });
});

const handleAlbumMediaDragEnter = (ev) => {
  ev.preventDefault();
};

const handleAlbumMediaDragLeave = (ev) => {
  ev.preventDefault();
  ev.currentTarget.classList.remove('media-item-grabbing-over');
};

const handleAlbumMediaDragOver = (ev) => {
  ev.preventDefault();
  ev.currentTarget.classList.add('media-item-grabbing-over');
};

const handleAlbumMediaDragEnd = (ev) => {
  document.querySelectorAll('.item-draggable .media-item').forEach(id => {
    id.removeEventListener('dragenter', handleAlbumMediaDragEnter);
    id.removeEventListener('dragover', handleAlbumMediaDragOver);
  });

  ev.currentTarget.classList.remove('media-item-grabbing-over');
  ev.currentTarget.classList.remove('media-item-grabbing');
};

const handleAlbumMediaDragStart = (ev) => {
  const sectionContainer = ev.currentTarget.parentElement.parentElement.parentElement;

  sectionContainer.querySelectorAll('.item-draggable .media-item').forEach(id => {
    id.addEventListener('dragenter', handleAlbumMediaDragEnter);
    id.addEventListener('dragover', handleAlbumMediaDragOver);
  });

  ev.dataTransfer.setData("text/plain", ev.currentTarget.id);
  ev.dataTransfer.dropEffect = "move";
  ev.effectAllowed = "move";
  ev.currentTarget.classList.add('media-item-grabbing');
};

const handleAlbumMediaDrop = (ev) => {
  ev.preventDefault();

  const sectionContainer = ev.currentTarget.parentElement.parentElement.parentElement;
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

  const loadingEl = document.querySelector('.drop-loading');
  draggedEl.parentElement.appendChild(loadingEl);
  loadingEl.classList.remove('null');

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

  const albumSectionId = sectionContainer.parentElement.parentElement.dataset.albumSectionId;
  const data = {
    sequenceTo: droppedSequence,
    albumSectionMediaIdTo: Number.parseInt(ev.currentTarget.dataset.albumSectionMediaId),
    sequenceFrom: draggedSequence,
    albumSectionMediaIdFrom: Number.parseInt(draggedEl.dataset.albumSectionMediaId),
    countDelta: countDelta,
  };

  Request.put('/back/album-section/' + albumSectionId + '/sequence', JSON.stringify(data))
    .then(() => {
      draggedEl.dataset.sequence = droppedSequence.toString();

      sectionContainer.querySelectorAll('.item-draggable .media-item').forEach(mi => {
        if (draggedEl.id === mi.id) {
          return;
        }

        const cSeq = Number.parseInt(mi.dataset.sequence);
        if ((cSeq >= droppedSequence && cSeq < draggedSequence) || (cSeq <= droppedSequence && cSeq > draggedSequence)) {
          mi.dataset.sequence = (cSeq + countDelta).toString();
        }
      });
      loadingEl.classList.add('null');
      draggedEl.classList.remove('media-item-grabbing');
    })
    .catch(error => Util.notifyError(error))
    .finally(() => {
      loadingEl.classList.add('null');
      draggedEl.classList.remove('media-item-grabbing');
    });
};

document.querySelectorAll('.item-draggable .media-item').forEach(f => {
  f.addEventListener('dragstart', handleAlbumMediaDragStart);

  f.addEventListener('dragleave', handleAlbumMediaDragLeave);
  f.addEventListener('dragend', handleAlbumMediaDragEnd);

  f.addEventListener('drop', handleAlbumMediaDrop);
});

export {handleDropdownOptionClick};
