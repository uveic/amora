import {Util} from './module/Util-007.js';
import {xhr} from './module/xhr-002.js';
import {global} from "./module/localisation-003.js";
import {Uploader} from "./module/Uploader-008.js";

let globalTags = [];

const feedbackDiv = document.querySelector('#feedback');

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

  const qty = 25;
  const existingImages = imagesContainer.querySelectorAll('.image-item');
  existingImages.forEach(img => {
    addEventListenerAction(img, img.dataset.mediaId, eventListenerAction, targetContainerId);
  });

  if (existingImages.length >= qty) {
    return;
  }

  loading.classList.remove('null');
  const typeId = button.dataset.typeId ? Number.parseInt(button.dataset.typeId) : '';

  xhr.get('/api/file?typeId=' + typeId + '&qty=' + qty)
    .then(response => {
      loading.classList.add('null');
      imagesContainer.classList.remove('null');
      displayImageFromApiCall(imagesContainer, response.files, eventListenerAction, targetContainerId);
    })
    .catch(error => {
      modal.classList.add('null');
      loading.classList.add('null');
      Util.logError(error);
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
        b.value = global.get('globalUpdate');
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
      feedbackDiv.textContent = global.get('feedbackSaving');
      feedbackDiv.classList.remove('feedback-error');
      feedbackDiv.classList.add('feedback-success');
      feedbackDiv.classList.remove('null');
      setTimeout(() => {feedbackDiv.classList.add('null')}, 5000);
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
      xhr.put(url + '/' + articleIdEl.value, payload, feedbackDiv, global.get('globalUpdated'))
        .then((res) => afterApiCall(res.articleId, res.articlePublicPath, res.articleBackofficePath));
    } else {
      xhr.post(url, payload, feedbackDiv, global.get('globalSaved'))
        .then((res) => afterApiCall(res.articleId, res.articlePublicPath, res.articleBackofficePath));
    }
  });
});

const handleCopyLink = (ev, href) => {
  ev.preventDefault();

  if (!navigator.clipboard) {
    Util.logError(new Error(global.get('feedbackCopyLinkError')));
    return;
  }

  navigator.clipboard.writeText(href).then(() => {
    Util.notifyUser(global.get('feedbackCopyLinkSuccess'));
  })
    .catch(error => Util.logError(error, global.get('feedbackCopyLinkError')));
};

const imageLoaded = (imageEl) => {
  imageEl.classList.remove('hidden');
  document.querySelector('.image-modal .image-loading').classList.add('null');
};

const displayImage = (image) => {
  const modal = document.querySelector('.image-modal');
  const content = modal.querySelector('.image-wrapper');
  const modalClose = modal.querySelector('.modal-close-button');
  let imageContainer = modal.querySelector('.image-main img');
  const imageInfoData = modal.querySelector('.image-info-data');
  const imageInfoNext = modal.querySelector('.image-next-wrapper');

  if (!imageContainer) {
    imageContainer = new Image();
    modal.querySelector('.image-main').appendChild(imageContainer);
  }

  if (!image) {
    document.querySelectorAll('.image-next-action').forEach(i => i.classList.add('hidden'));
    modal.querySelector('.image-loading').classList.add('null');
    content.classList.remove('null');
    modalClose.classList.remove('null');
    imageContainer.classList.remove('hidden');
    imageInfoData.classList.remove('null');
    imageInfoNext.classList.remove('null');

    return;
  }

  const alt = image.caption ?? image.name;
  imageContainer.src = image.pathMedium;
  imageContainer.alt = alt;
  imageContainer.title = alt;
  imageContainer.dataset.mediaId = image.id;
  imageContainer.addEventListener('load', () => imageLoaded(imageContainer));

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
    + global.formatDate(new Date(image.exif.date), true, true, true, true, true);
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

  modal.querySelector('.image-number').textContent = '#' + image.id;
  modal.querySelector('.image-caption').textContent = image.caption;
  modal.querySelector('.image-meta').innerHTML =
    '<div><img src="/img/svg/upload-simple-white.svg" class="img-svg" alt="Upload">'
    + global.formatDate(new Date(image.createdAt), true, true, true, true, true)
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

  modal.querySelector('.image-meta .copy-link')
    .addEventListener('click', e => handleCopyLink(e, image.fullPathMedium));

  const imageDeleteEl = modal.querySelector('.image-delete');
  imageDeleteEl.dataset.mediaId = image.id;
  const appearsOnContainer = modal.querySelector('.image-appears-on');
  appearsOnContainer.innerHTML = '';
  if (image.appearsOn && image.appearsOn.length) {
    imageDeleteEl.classList.add('null');

    const appearsTitle = document.createElement('h3');
    appearsTitle.textContent = global.get('globalAppearsOn') + ':';
    appearsOnContainer.appendChild(appearsTitle);

    image.appearsOn.forEach(ao => {
      const appearsLink = document.createElement('a');
      appearsLink.href = ao.path;
      appearsLink.target = '_blank';
      appearsLink.textContent = ao.title;
      const appearsInfo = document.createElement('span');
      appearsInfo.innerHTML = '<img src="/img/svg/calendar-white.svg" class="img-svg m-r-025" alt="Calendar">'
        + global.formatDate(new Date(ao.publishedOn), false, false, true, false, false);
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

const displayImagePopup = (e) => {
  e.preventDefault();

  const mediaId = e.currentTarget.mediaId;
  const next = e.currentTarget.next ?? false;
  const direction = e.currentTarget.direction ?? null;

  const modal = document.querySelector('.image-modal');
  const loadingContainer = modal.querySelector('.image-loading');
  const modalClose = modal.querySelector('.modal-close-button');
  const imageContainer = modal.querySelector('.image-main img');
  const imageInfoData = modal.querySelector('.image-info-data');
  const imageInfoNext = modal.querySelector('.image-next-wrapper');

  modal.classList.remove('null');
  loadingContainer.classList.remove('null');
  modalClose.classList.add('null');
  if (imageContainer) {
    imageContainer.classList.add('hidden');
  }
  if (imageInfoData) {
    imageInfoData.classList.add('null');
  }
  if (imageInfoNext) {
    imageInfoNext.classList.add('null');
  }

  const qty = 1;
  const typeId = 2; // See MediaType.php
  const apiUrl = next
    ? '/api/file/' + mediaId + '/next?direction=' + direction + '&typeId=' + typeId + '&qty=' + qty
    : '/api/file/' + mediaId
  xhr.get(apiUrl)
    .then(response => {
      next === true
        ? displayImage(response.files[0] ?? null)
        : displayImage(response.file ?? null);
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

  imageCaption.scrollIntoView({behavior: 'smooth', block: 'start' });
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

  document.querySelector('.select-media-action span').textContent = global.get('globalModify');
  document.querySelector('.select-media-modal').classList.add('null');
  document.querySelector('.article-main-image-wrapper').scrollIntoView({behavior: 'smooth', block: 'start' });
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

  e.currentTarget.textContent = global.get('globalModify');
  document.querySelector('.select-media-modal').classList.add('null');
  imageContainer.scrollIntoView({behavior: 'smooth', block: 'start' });
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
  actionButton.scrollIntoView({behavior: 'smooth', block: 'center' });

  xhr.post('/back/album-section/' + albumSectionId + '/media', JSON.stringify(payload))
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
      } else {
        Util.notifyUser('A imaxe xa fora engadida.');
      }
    })
    .catch(error => {
      container.removeChild(container.querySelector('img[data-media-id="' + mediaId + '"]'));
      Util.logError(error);
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

  if (titleEl.textContent.trim() === '-') {
    titleEl.textContent = '';
  }

  if (subtitleEl.textContent.trim() === '-') {
    subtitleEl.textContent = '';
  }

  if (contentEl.textContent.trim() === '-') {
    contentEl.textContent = '';
  }

  titleEl.contentEditable = true;
  titleEl.classList.add('album-content-editable');
  subtitleEl.contentEditable = true;
  subtitleEl.classList.add('album-content-editable');
  contentEl.contentEditable = true;
  contentEl.classList.add('album-content-editable');
  container.querySelector('.main-image-button-container').classList.remove('null');
  container.querySelector('.album-section-button-container-js').classList.remove('null');
  e.currentTarget.classList.add('null');
  titleEl.focus();
};

const updateAlbumSection = (e) => {
  e.preventDefault();

  const albumSectionId = e.currentTarget.dataset.albumSectionId;

  cancelAlbumSectionEdit(e);

  const container = document.querySelector('.album-section-item[data-album-section-id="' + albumSectionId + '"]');
  const titleHtml = container.querySelector('.section-title-html').textContent.trim();
  const subtitleHtml = container.querySelector('.section-subtitle-html').textContent.trim();
  const contentHtml = container.querySelector('.section-content-html').textContent.trim();
  const mainMedia = container.querySelector('img.album-section-main-media');
  const mainMediaId = mainMedia ? Number.parseInt(mainMedia.dataset.mediaId) : null;

  const payload = {
    titleHtml: titleHtml === '-' ? '' : titleHtml,
    subtitleHtml: subtitleHtml === '-' ? '' : subtitleHtml,
    contentHtml: contentHtml === '-' ? '' : contentHtml,
    mainMediaId: mainMediaId,
  };

  xhr.put('/back/album-section/' + albumSectionId, JSON.stringify(payload))
    .catch(error => {
      Util.logError(error);
      makeAlbumSectionNonEditable(albumSectionId);
    });
};

const makeAlbumSectionNonEditable = (albumSectionId) => {
  const container = document.querySelector('.album-section-item[data-album-section-id="' + albumSectionId + '"]');
  const titleEl = container.querySelector('.section-title-html');
  const subtitleEl = container.querySelector('.section-subtitle-html');
  const contentEl = container.querySelector('.section-content-html');
  const mediaButtonsContainer = container.querySelector('.main-image-button-container');
  const editButton = container.querySelector('.album-section-edit-js');

  if (titleEl.textContent.trim() === '') {
    titleEl.textContent = '-';
  }

  if (subtitleEl.textContent.trim() === '') {
    subtitleEl.textContent = '-';
  }

  if (contentEl.textContent.trim() === '') {
    contentEl.textContent = '-';
  }

  titleEl.contentEditable = false;
  titleEl.classList.remove('album-content-editable');
  subtitleEl.contentEditable = false;
  subtitleEl.classList.remove('album-content-editable');
  contentEl.contentEditable = false;
  contentEl.classList.remove('album-content-editable');
  mediaButtonsContainer.classList.add('null');
  editButton.classList.remove('null');
  container.querySelector('.album-section-button-container-js').classList.add('null');
};

const cancelAlbumSectionEdit = (e) => {
  e.preventDefault();

  const albumSectionId = e.currentTarget.dataset.albumSectionId;

  makeAlbumSectionNonEditable(albumSectionId);
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

  container.querySelector('.album-section-main-media-js span').textContent = global.get('globalModify');
  container.querySelector('.album-section-main-media-delete-js').classList.remove('null');
  document.querySelector('.select-media-modal').classList.add('null');
};

const albumSectionDeleteMainMedia = (e) => {
  e.preventDefault();

  const delRes = confirm(global.get('feedbackDeleteGeneric'));
  if (!delRes) {
    return;
  }

  const targetContainerId = e.currentTarget.targetContainerId;

  const container = document.querySelector('#' + targetContainerId);
  const targetImg = container.querySelector('.album-section-main-media');

  if (targetImg) {
    container.removeChild(targetImg);
  }

  container.querySelector('.album-section-main-media-delete-js').classList.add('null');
  container.querySelector('.album-section-main-media-js span').textContent = global.get('globalSelectImage');
};

const albumSectionDeleteMedia = (e) => {
  e.preventDefault();

  const delRes = confirm(global.get('feedbackDeleteGeneric'));
  if (!delRes) {
    return;
  }

  const mediaId = e.currentTarget.mediaId;
  const albumSectionMediaId = e.currentTarget.dataset.albumSectionMediaId;
  const targetContainerId = e.currentTarget.targetContainerId;

  const container = document.querySelector('#' + targetContainerId);
  const targetMediaContainer = container.querySelector('img[data-media-id="' + mediaId + '"]');

  targetMediaContainer.parentElement.parentElement.classList.add('null');

  xhr.delete('/back/album-section-media/' + albumSectionMediaId, null, feedbackDiv)
    .catch(error => {
      targetMediaContainer.parentElement.parentElement.classList.remove('null');
      Util.logError(error);
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
  const existingMedia = e.currentTarget.parentElement.querySelector('img[data-media-id="' + mediaId + '"]');
  const sectionContentContainer = document.querySelector(
    '.album-section-item[data-album-section-id="' + albumSectionId + '"]'
  );
  const titleText = sectionContentContainer.querySelector('.section-title-html').textContent;
  const subtitleText = sectionContentContainer.querySelector('.album-section-item-number').textContent
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

const addEventListenerAction = (image, mediaId, eventListenerAction, targetContainerId) => {
  if (eventListenerAction === 'displayImagePopup') {
    image.addEventListener('click', displayImagePopup);
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
  im.addEventListener('click', displayImagePopup);
});

document.querySelectorAll('.image-next-action, .image-previous-action, .image-random-action').forEach(ina => {
  ina.addEventListener('click', () => {
    const img = document.querySelector('.image-wrapper .image-main img');
    img.classList.add('hidden');
    img.mediaId = img.dataset.mediaId;
    img.direction = ina.dataset.direction
    img.next = true;
    img.addEventListener('click', displayImagePopup);
    img.click();
  });
});

const deleteImage = async function (e, mediaId) {
  e.preventDefault();

  const delRes = confirm(global.get('feedbackDeleteImageConfirmation'));
  if (!delRes) {
    return;
  }

  xhr.delete('/api/file/' + mediaId, null, feedbackDiv)
    .then(() => {
      document.querySelector(".image-item[data-media-id='" + mediaId + "']").classList.add('null');
      document.querySelector('.image-modal').classList.add('null');
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

    for (let i = 0; i < im.files.length; i++) {
      let file = im.files[i];

      Uploader.uploadImage(
        file,
        container,
        'image-item',
        feedbackDiv,
        (response) => {
          if (response && response.file.id) {
            const newImage = container.querySelector('.image-item[data-media-id="' + response.file.id + '"]');
            newImage.addEventListener('click', displayImagePopup);
            newImage.mediaId = response.file.id;
          }
        },
      );
    }
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
        feedbackDiv,
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
            mediaInfo.textContent = global.get('globalUploadedOn') + ' '
              + global.formatDate(new Date(response.file.createdAt), true, true, true, true, true)
              + ' ' + global.get('globalBy') + ' ' + response.file.userName + '.';

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
      xhr.put('/back/user/' + userId, payload, feedbackDiv)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        });
    } else {
      xhr.post('/back/user', payload, feedbackDiv)
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
      xhr.get('/back/tag')
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
      xhr.get('/back/article/' + articleId + '/previous-path')
        .then(response => {
          response.paths.forEach(p => {
            const newPathEl = document.createElement('div');
            newPathEl.dataset.pathId = p.id;
            newPathEl.innerHTML = '<span>' + p.path + '</span><span>'
              + global.formatDate(new Date(p.createdAt), false) + '</span>';
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
    imgClose.title = global.get('globalRemove');
    imgClose.alt = global.get('globalRemove');
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

  xhr.post('/back/tag', JSON.stringify({name: tagName}))
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

    xhr.get('/api/file/' + lastImageId + '/next?direction=' + dir + '&typeId=' + typeId + '&qty=' + qty)
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
    const feedbackDiv = document.querySelector('#feedback');
    const eventListenerAction = im.dataset.eventListenerAction;
    const targetContainerId = im.dataset.targetContainerId;

    for (let i = 0; i < im.files.length; i++) {
      let file = im.files[i];

      Uploader.uploadImage(
        file,
        container,
        'image-item',
        feedbackDiv,
        (response) => {
          displayImageFromApiCall(container, [response.file], eventListenerAction, targetContainerId);
        },
      );
    }
  });
});

document.querySelectorAll('.editor-title').forEach(t => {
  t.addEventListener('focus', () => {
    const content = t.textContent.trim();

    t.classList.remove('editor-placeholder');

    if (content === global.get('editorTitlePlaceholder')) {
      t.innerHTML = '';
    }
  });

  t.addEventListener('blur', () => {
    const content = t.textContent.trim();

    if (!content.length) {
      t.innerHTML = global.get('editorTitlePlaceholder');
      t.classList.add('editor-placeholder');
    }
  });
});

document.querySelectorAll('.editor-subtitle').forEach(t => {
  t.addEventListener('focus', () => {
    const content = t.textContent.trim();

    t.classList.remove('editor-placeholder');

    if (content === global.get('editorSubtitlePlaceholder')) {
      t.innerHTML = '';
    }
  });

  t.addEventListener('blur', () => {
    const content = t.textContent.trim();

    if (!content.length) {
      t.innerHTML = global.get('editorSubtitlePlaceholder');
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
        ? (titleHtml === global.get('editorTitlePlaceholder') ? null : titleHtml)
        : null,
      subtitleHtml: subtitleHtml.length
        ? (subtitleHtml === global.get('editorSubtitlePlaceholder') ? null : subtitleHtml)
        : null,
      contentHtml: contentHtml,
      mainImageId: mainImageEl && mainImageEl.dataset.mediaId
        ? Number.parseInt(mainImageEl.dataset.mediaId)
        : null,
    });

    xhr.put('/back/content/' + contentId, payload, feedbackDiv)
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

    const videoUrl = window.prompt(global.get('editorVideoUrlTitle'));
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
      Util.logError(new Error('Media image is missing'));
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
      xhr.put('/back/album/' + albumId, payload, feedbackDiv)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        });
    } else {
      xhr.post('/back/album', payload, feedbackDiv)
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

    xhr.put('/back/album/' + albumId + '/status/' + statusId, '')
      .then((response) => {
        handleDropdownOptionClick(op, 'album-status');

        document.querySelector('.form-public-link .value').innerHTML = response.publicLinkHtml;
      })
      .catch((error) => {
        document.querySelector('#event-status-dd-checkbox').checked = false;
        Util.logError(error);
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

    xhr.post('/back/album/' + albumId + '/section', '')
      .then(response => {
        container.insertAdjacentHTML('beforeend', response.html);
        const sectionContainer = container.querySelector('.album-section-item[data-album-section-id="' + response.newSectionId + '"]');
        sectionContainer.querySelector('.select-media-action').addEventListener('click', selectMediaAction);
        sectionContainer.querySelector('.album-section-edit-js').addEventListener('click', editAlbumSection);
        sectionContainer.querySelector('.album-section-save-js').addEventListener('click', updateAlbumSection);
        sectionContainer.scrollIntoView({behavior: 'smooth', block: 'center' });
      })
      .catch(error => {
        Util.logError(error);
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

    xhr.post('/back/album/' + albumId + '/section', JSON.stringify(payload))
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

    sectionMediaContainer.querySelectorAll('.album-section-media-container .image-item').forEach(i => {
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

    xhr.put('/back/album-section-media/' + albumSectionMediaId, payload, feedbackDiv)
      .catch(error => {
        targetCaptionHtmlEl.textContent = captionHtmlBefore;
        Util.logError(error);
      });
  });
});

export {handleDropdownOptionClick};
