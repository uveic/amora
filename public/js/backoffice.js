import {getSectionTypeIdFromClassList, cleanString, getUpdatedAtTime} from './module/util.js';
import {xhr} from './module/xhr.js';
import {feedbackDiv} from './authorised.js';
import {global} from "./module/localisation.js";
import {PexegoEditor as Pexego, pexegoClasses} from "./module/Pexego.js";
import {uploadFile, uploadImage} from "./module/uploader.js";

let globalTags = [];

document.querySelectorAll('.article-save-button').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const afterApiCall = function(articleId, articlePublicUri, articleBackofficeUri) {
      history.pushState("", document.title, articleBackofficeUri);
      document.querySelector('input[name="articleId"]').value = articleId;

      document.querySelectorAll('.control-bar-creation').forEach(a => a.classList.remove('hidden'));
      document.querySelectorAll('span.article-updated-at').forEach(s => {
        s.textContent = getUpdatedAtTime();
      });

      document.querySelectorAll('#side-options').forEach(i => i.classList.add('null'));
      document.querySelectorAll('.article-save-button').forEach(b => {
        b.value = global.get('globalUpdate');
      });
      document.querySelectorAll('.pexego-preview').forEach(b => {
        b.href = articlePublicUri;
        b.classList.remove('null');
      });
      document.querySelectorAll('input[name="articleUri"]').forEach(i => {
        i.value = articlePublicUri.trim().replace(/^\//,"");
      });
    };

    const getTitleContent = () => {
      const titleEl = document.querySelector('input[name="articleTitle"]');
      if (titleEl && titleEl.value.trim().length) {
        return titleEl.value.trim();
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
      let articleContentHtml = '';
      let sections = [];
      let order = 1;
      let mainImageId = null;

      document.querySelectorAll('.pexego-section').forEach(originalSection => {
        let section = originalSection.cloneNode(true);
        let editorId = section.dataset.sectionId ?? section.dataset.editorId;

        let sectionElement = section.classList.contains(pexegoClasses.sectionParagraph)
          ? document.querySelector('#' + pexegoClasses.sectionParagraph + '-' + editorId + '-html')
          : section;

        let sectionContentHtml = sectionElement.innerHTML.trim();

        for (let i = 0; i < sectionElement.children.length; i++) {
          let c = sectionElement.children[i];

          if (c.classList.contains(pexegoClasses.contentImageCaption)) {
            c.classList.add('article-section-image-caption');
            if (c.textContent.trim() === c.dataset.placeholder) {
              c.textContent = '';
            }
          }

          if (c.classList.contains(pexegoClasses.contentParagraph)
            && c.textContent.trim() === c.dataset.placeholder
          ) {
            c.textContent = '';
          }

          c.classList.remove(
            pexegoClasses.contentParagraph,
            pexegoClasses.contentImage,
            pexegoClasses.contentImageCaption
          );
          if (!c.classList.length) {
            c.removeAttribute('class');
          }
          c.removeAttribute('id');
          c.removeAttribute('contenteditable');
          delete c.dataset.placeholder;
          delete c.dataset.imageId;

          if (c.nodeName !== 'IMG' && !c.textContent.trim().length) {
            sectionElement.removeChild(c);
          }
        }

        let elementContent = sectionElement.innerHTML.trim();
        if (elementContent.length) {
          articleContentHtml += elementContent;
        }

        let currentSection = {
          id: section.dataset.sectionId ? Number.parseInt(section.dataset.sectionId) : null,
          sectionTypeId: getSectionTypeIdFromClassList(section.classList),
          contentHtml: sectionContentHtml,
          order: order++
        };

        if (section.classList.contains(pexegoClasses.sectionImage)) {
          const imageCaption = originalSection.querySelector('.' + pexegoClasses.contentImageCaption);
          currentSection.imageCaption = imageCaption
            && imageCaption.textContent.length
            && imageCaption.dataset.placeholder !== imageCaption.textContent.trim()
              ? imageCaption.textContent.trim()
              : null;

          const image = originalSection.querySelector('.' + pexegoClasses.contentImage);
          currentSection.imageId = image ? Number.parseInt(image.dataset.imageId) : null;
          if (!mainImageId && currentSection.imageId) {
            mainImageId = currentSection.imageId;
          }
        }

        sections.push(currentSection);
      });

      return {
        sections: sections,
        contentHtml: articleContentHtml.trim().length ? articleContentHtml.trim() : null,
        mainImageId: mainImageId,
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

    const getUri = () => {
      const uriEl = document.querySelector('div.article-uri-value');
      return uriEl && uriEl.textContent.trim().length ? uriEl.textContent.trim() : null;
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

    if (!articleTitle && !content.contentHtml) {
      feedbackDiv.textContent = global.get('feedbackSaving');
      feedbackDiv.classList.remove('feedback-error');
      feedbackDiv.classList.add('feedback-success');
      feedbackDiv.classList.remove('null');
      setTimeout(() => {feedbackDiv.classList.add('null')}, 5000);
      document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
      return;
    }

    document.querySelectorAll('.article-saving').forEach(ar => ar.classList.remove('null'));

    const payload = JSON.stringify({
      siteLanguageIsoCode: document.documentElement.lang ?? articleLanguageIsoCode,
      articleLanguageIsoCode: articleLanguageIsoCode,
      title: articleTitle,
      uri: getUri(),
      contentHtml: content.contentHtml,
      typeId: getArticleTypeId(),
      statusId: getStatusId(),
      mainImageId: content.mainImageId,
      sections: content.sections,
      tags: getTags(),
      publishOn: getPublishOnDateIsoString(),
    });

    const articleIdEl = document.querySelector('input[name="articleId"]');
    const url = '/back/article';
    if (articleIdEl && articleIdEl.value) {
      xhr.put(url + '/' + articleIdEl.value, payload, feedbackDiv, global.get('globalUpdated'))
        .then((res) => afterApiCall(res.articleId, res.articlePublicUri, res.articleBackofficeUri))
        .finally(() => {
          document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
        });
    } else {
      xhr.post(url, payload, feedbackDiv, global.get('globalSaved'))
        .then((res) => afterApiCall(res.articleId, res.articlePublicUri, res.articleBackofficeUri))
        .finally(() => {
          document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
        });
    }
  });
});

const displayImage = (image) => {
  const modal = document.querySelector('.image-modal');
  const loadingContainer = modal.querySelector('.image-modal-loading');
  const content = modal.querySelector('.image-wrapper');
  const modalClose = modal.querySelector('.modal-close-button');
  let imageContainer = modal.querySelector('.image-main img');

  if (!imageContainer) {
    imageContainer = new Image();
    modal.querySelector('.image-main').appendChild(imageContainer);
  }

  if (!image) {
    loadingContainer.classList.add('null');
    content.classList.remove('null');
    modalClose.classList.remove('null');

    return;
  }

  const alt = image.caption ?? image.name;
  imageContainer.src = image.uri;
  imageContainer.alt = alt;
  imageContainer.title = alt;
  imageContainer.dataset.imageId = image.id;

  // Hide/display image nav buttons
  const firstImageEl = document.querySelector('#images-list .image-item');
  const firstImageId = firstImageEl ? Number.parseInt(firstImageEl.dataset.imageId) : null;
  const lastImageEl = document.querySelector('#images-list .image-item:last-child');
  const lastImageId = lastImageEl ? Number.parseInt(lastImageEl.dataset.imageId) : null;
  firstImageId !== image.id
    ? document.querySelectorAll('.image-previous-action').forEach(i => i.classList.remove('hidden'))
    : document.querySelectorAll('.image-previous-action').forEach(i => i.classList.add('hidden'));

  lastImageId !== image.id
    ? document.querySelectorAll('.image-next-action').forEach(i => i.classList.remove('hidden'))
    : document.querySelectorAll('.image-next-action').forEach(i => i.classList.add('hidden'));

  modal.querySelector('.image-title').textContent = '#' + image.id;
  modal.querySelector('.image-caption').textContent = image.caption;
  modal.querySelector('.image-meta').innerHTML =
    '<span><img src="/img/svg/upload-simple-white.svg" class="img-svg m-r-05">'
    + global.formatDate(new Date(image.createdAt), true, true, true, true, true)
    + '</span><span>'
    + '<img src="/img/svg/user-white.svg" class="img-svg m-r-05">' + image.userName
    + '</span><span class="image-uri">'
    + '<img src="/img/svg/link-white.svg" class="img-svg m-r-05">' + image.uri
    + '</span>';

  const appearsOnContainer = modal.querySelector('.image-appears-on');
  appearsOnContainer.innerHTML = '';
  if (image.appearsOn && image.appearsOn.length) {
    image.appearsOn.forEach(ao => {
      const appearsTitle = document.createElement('h3');
      appearsTitle.textContent = global.get('globalAppearsOn') + ':';
      const appearsLink = document.createElement('a');
      appearsLink.href = ao.uri;
      appearsLink.target = '_blank';
      appearsLink.textContent = ao.title;
      const appearsInfo = document.createElement('span');
      appearsInfo.innerHTML = '<img src="/img/svg/calendar-white.svg" class="img-svg m-r-025">'
        + global.formatDate(new Date(ao.publishedOn), false, false, true, false, false);
      appearsLink.appendChild(appearsInfo);
      appearsOnContainer.appendChild(appearsTitle);
      appearsOnContainer.appendChild(appearsLink);
    });
  }

  loadingContainer.classList.add('null');
  content.classList.remove('null');
  modalClose.classList.remove('null');
};

const displayImagePopup = (e, imageId, next = false, direction = null) => {
  e.preventDefault();

  const modal = document.querySelector('.image-modal');
  const loadingContainer = modal.querySelector('.image-modal-loading');
  const content = modal.querySelector('.image-wrapper');
  const modalClose = modal.querySelector('.modal-close-button');

  modal.classList.remove('null');
  loadingContainer.classList.remove('null');
  content.classList.add('null');
  modalClose.classList.add('null');

  const qty = 1;
  const typeId = 2; // See MediaType.php
  const apiUrl = next
    ? '/api/file/' + imageId + '/next?direction=' + direction + '&typeId=' + typeId + '&qty=' + qty
    : '/api/file/' + imageId
  xhr.get(apiUrl)
    .then(response => {
      next === true
        ? displayImage(response.files[0] ?? null)
        : displayImage(response.file ?? null);
    });
};

const insertImageInArticle = (imageEl) => {
  let newImage = new Image();
  newImage.className = pexegoClasses.contentImage;
  newImage.src = imageEl.src;
  newImage.dataset.imageId = imageEl.dataset.imageId;
  newImage.alt = imageEl.alt;
  newImage.title = imageEl.title;

  let sectionId = Pexego.generateRandomString(5);
  let pexegoSectionImage = document.createElement('section');
  pexegoSectionImage.className =  pexegoClasses.section + ' ' + pexegoClasses.sectionImage;

  let imageCaption = document.createElement('div');
  imageCaption.dataset.placeholder = global.get('editorImageCaptionPlaceholder');
  imageCaption.contentEditable = 'true';
  imageCaption.innerHTML = '<p>' + imageCaption.dataset.placeholder + '</p>';
  imageCaption.classList.add(pexegoClasses.contentImageCaption);
  imageCaption.classList.add(pexegoClasses.sectionParagraphPlaceholder);
  imageCaption.addEventListener('focus', Pexego.displayPlaceholderFocus);
  imageCaption.addEventListener('blur', Pexego.displayPlaceholderBlur);

  pexegoSectionImage.appendChild(newImage);
  pexegoSectionImage.appendChild(imageCaption);

  Pexego.generateSectionWrapperFor(pexegoSectionImage, sectionId);

  document.querySelector('.add-image-modal').classList.add('null');
};

const displayImageFromApiCall = (container, images, eventListenerAction) => {
  images.forEach(image => {
    const existingImage = container.querySelector('img[data-image-id="' + image.id + '"]');
    if (existingImage) {
      return;
    }

    const imageEl = new Image();
    imageEl.src = image.uri;
    const alt = image.caption ?? image.name;
    imageEl.alt = alt;
    imageEl.title = alt;
    imageEl.dataset.imageId = image.id;
    imageEl.className = 'image-item';
    imageEl.loading = 'lazy';
    if (eventListenerAction === 'displayImagePopup') {
      imageEl.addEventListener('click', e => displayImagePopup(e, image.id));
    } else if (eventListenerAction === 'insertImageInArticle') {
      imageEl.addEventListener('click', () => insertImageInArticle(imageEl));
    }
    container.appendChild(imageEl);
  });
};

document.querySelectorAll('.image-item').forEach(im => {
  im.addEventListener('click', e => displayImagePopup(e, im.dataset.imageId));
});

document.querySelectorAll('.image-next-action, .image-previous-action').forEach(ina => {
  ina.addEventListener('click', e => {
    const imageId = document.querySelector('.image-wrapper .image-main img').dataset.imageId;
    const direction = ina.dataset.direction;

    displayImagePopup(e, imageId, true, direction);
  });
});

const deleteImage = async function (e, imageId) {
  e.preventDefault();

  const delRes = confirm(global.get('feedbackDeleteImageConfirmation'));
  if (!delRes) {
    return;
  }

  xhr.delete('/api/file/' + imageId, null, feedbackDiv)
    .then(() => {
      document.querySelector(".image-item[data-image-id='" + imageId + "']").classList.add('null');
      document.querySelector('.image-modal').classList.add('null');
    });
}

document.querySelectorAll('.image-delete').forEach(imgEl => {
  imgEl.addEventListener('click', e => {
    const imageId = document.querySelector('.image-wrapper .image-main img').dataset.imageId;
    deleteImage(e, imageId).then();
  });
});

document.querySelectorAll('input#images').forEach(im => {
  im.addEventListener('change', e => {
    e.preventDefault();

    const container = document.querySelector('#images-list');

    for (let i = 0; i < im.files.length; i++) {
      let file = im.files[i];

      let newImageContainer = document.createElement('a');
      newImageContainer.href = '#';
      newImageContainer.className = 'image-item';
      container.insertBefore(newImageContainer, container.firstChild);

      uploadImage(
        file,
        newImageContainer,
        '',
        feedbackDiv,
        (response) => {
          if (response && response.file.id) {
            newImageContainer.dataset.imageId = response.file.id;
            newImageContainer.addEventListener('click', e => displayImagePopup(e, response.file.id));
          }
        },
        () => container.removeChild(newImageContainer),
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

      uploadFile(
        file,
        newMediaContainer,
        '',
        feedbackDiv,
        (response) => {
          if (response && response.file.id) {
            newMediaContainer.dataset.mediaId = response.file.id;
            newMediaContainer.href = response.file.uri;

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
    const isEnabledEl = document.querySelector('.user-status-dd-option[data-checked="1"]');
    const isEnabled = Number.parseInt(isEnabledEl.dataset.value) > 0;

    const userId = userIdEl && userIdEl.value ? Number.parseInt(userIdEl.value) : null;

    const payload = JSON.stringify({
      name: nameEl.value ?? null,
      email: emailEl.value ?? null,
      bio: bioEl.value ?? null,
      languageIsoCode: languageIsoCodeEl.value ?? null,
      roleId: roleIdEl.value ?? null,
      timezone: timezoneEl.value ?? null,
      isEnabled: isEnabled,
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

    const uriContainer = document.querySelector('div.article-edit-previous-uri-container');
    uriContainer.querySelector('img').classList.remove('null');
    uriContainer.querySelectorAll('div').forEach(di => uriContainer.removeChild(di));

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
    xhr.get('/back/article/' + articleId + '/previous-uri')
      .then(response => {
        response.uris.forEach(u => {
          const newUriEl = document.createElement('div');
          newUriEl.dataset.uriId = u.id;
          newUriEl.innerHTML = '<span>' + u.uri + '</span><span>'
            + global.formatDate(new Date(u.createdAt), false) + '</span>';
          newUriEl.className = 'article-edit-previous-uri';
          uriContainer.appendChild(newUriEl);
        });

        uriContainer.querySelector('img').classList.add('null');
      });
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
    const cleanInput = cleanString(inputText);
    allResults.forEach(r => {
      if (r.dataset.new) {
        r.parentNode.removeChild(r);
        count--;
        return;
      }

      if (cleanString(r.textContent).includes(cleanInput)) {
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

document.querySelectorAll('.pexego-rearrange-sections-button').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('.pexego-actions-amora').forEach(i => i.classList.add('null'));
    document.querySelectorAll('.pexego-rearrange-sections-close').forEach(i => i.classList.remove('null'));
    document.querySelectorAll('.pexego-section-controls').forEach(d => d.classList.remove('null'));
  });
});

document.querySelectorAll('.pexego-rearrange-sections-close').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('.pexego-actions-amora').forEach(i => i.classList.remove('null'));
    document.querySelectorAll('.pexego-rearrange-sections-close').forEach(i => i.classList.add('null'));
    document.querySelectorAll('.pexego-section-controls').forEach(d => d.classList.add('null'));
  });
});

document.querySelectorAll('#filter-close').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelector('#filter-container').classList.add('null');
  });
});

document.querySelectorAll('#filter-open').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelector('#filter-container').classList.remove('null');
  });
});

document.querySelectorAll('#filter-refresh').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = window.location.origin + window.location.pathname;
  });
});

document.querySelectorAll('#filter-article-refresh').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    const articleTypeId = document.querySelector('select[name="articleType"]').value;
    window.location.href = window.location.origin + window.location.pathname + '?atId=' + articleTypeId;
  });
});

document.querySelectorAll('#filter-article-button').forEach(a => {
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
      document.querySelector('#filter-container').classList.remove('null');
      return;
    }

    const queryString = query.entries() ? '?' + query.toString() : '';

    window.location.href = window.location.origin + window.location.pathname + queryString;
  });
});

document.addEventListener('keydown', e => {
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
  }
});

document.querySelectorAll('.media-load-more').forEach(lm => {
  lm.addEventListener('click', e => {
    e.preventDefault();

    lm.disabled = true;
    const lastImageEl = document.querySelector('#images-list .image-item:last-child');
    const lastImageId = lastImageEl ? Number.parseInt(lastImageEl.dataset.imageId) : null;
    const qty = 25;
    const typeId = lm.dataset.typeId ? Number.parseInt(lm.dataset.typeId) : '';
    const dir = lm.dataset.direccion ?? '';

    xhr.get('/api/file/' + lastImageId + '/next?direction=' + dir + '&typeId=' + typeId + '&qty=' + qty)
      .then(response => {
        const container = document.querySelector('#images-list');

        displayImageFromApiCall(container, response.files, 'displayImagePopup');

        lm.disabled = false;

        if (response.files.length < qty) {
          lm.classList.add('null');
        }
      });
  });
});

document.querySelectorAll('.article-add-media').forEach(am => {
  am.addEventListener('click', e => {
    e.preventDefault();

    const modal = document.querySelector('.add-image-modal');
    modal.classList.remove('null');

    xhr.get('/api/file')
      .then(response => {
        modal.querySelector('.add-image-modal-loading').classList.add('null');
        const container = modal.querySelector('#images-list');

        displayImageFromApiCall(container, response.files, 'insertImageInArticle');

        container.classList.remove('null');
      });
  });
});

document.querySelectorAll('input[name="article-add-media-upload"]').forEach(im => {
  im.addEventListener('change', e => {
    e.preventDefault();

    const container = document.querySelector('#images-list');

    for (let i = 0; i < im.files.length; i++) {
      let file = im.files[i];

      let newImageContainer = document.createElement('a');
      newImageContainer.href = '#';
      newImageContainer.className = 'image-item';
      container.insertBefore(newImageContainer, container.firstChild);

      uploadImage(
        file,
        container,
        '',
        feedbackDiv,
        (response) => {
          if (response && response.file.id) {
            displayImageFromApiCall(newImageContainer, [response.file], 'insertImageInArticle');
          }
        },
        () => container.removeChild(newImageContainer),
      );
    }
  });
});
