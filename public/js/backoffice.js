import {
  cleanTextForUrl,
  getUpdatedAtTime,
  getYoutubeVideoIdFromUrl,
  managePlaceholderForEditableElements,
  generateRandomString,
  insertAfter
} from './module/util.js';
import {xhr} from './module/xhr.js';
import {feedbackDiv} from './authorised.js';
import {loadEditor} from './module/editor.js';
import {global} from "./module/localisation.js";

const removeSection = function(e, sectionId) {
  e.preventDefault();

  const delRes = confirm(global.get('feedbackDeleteSectionConfirmation'));
  if (!delRes) {
    return;
  }

  const article = document.querySelector('.article-content');
  const section = document.querySelector('#article-section-wrapper-' + sectionId);

  if (article && section) {
    article.removeChild(section);
  }

  if (section.querySelector('#article-title-main')) {
    const allTitles = document.querySelectorAll('h1.article-title');
    if (allTitles.length > 0) {
      allTitles[0].id = 'article-title-main';
      allTitles[0].addEventListener('input', (e) => updateArticleUri(e));
      allTitles[0].dispatchEvent(new Event('input', {bubbles: true, cancelable: true}));
    }
  }

  const sectionHtml = document.querySelector('#article-section-paragraph-' + sectionId + '-html');
  if (sectionHtml) {
    const divHtml = document.querySelector('.article-content-text');
    if (divHtml) {
      divHtml.removeChild(sectionHtml);
    }
  }

  displayUpAndDownArrows();
};

const moveSectionUp = function(e, id) {
  e.preventDefault();

  const selectedSection = document.querySelector('#article-section-wrapper-' + id);

  let previousElement = null;

  let allSections = [];
  document.querySelectorAll('.article-section-wrapper').forEach(s => {
    allSections.push({id: s.dataset.sectionId, element: s});
  });

  for (let i = 0; i < allSections.length; i++) {
    if (allSections[i].id === id) {
      if (previousElement) {
        selectedSection.parentNode.insertBefore(selectedSection, previousElement);
      }
      break;
    }

    previousElement = allSections[i].element;
  }

  displayUpAndDownArrows();
};

const moveSectionDown = function(e, id) {
  e.preventDefault();

  const selectedSection = document.querySelector('#article-section-wrapper-' + id);

  if (!selectedSection.nextElementSibling) {
    return;
  }

  insertAfter(selectedSection, selectedSection.nextElementSibling);
  displayUpAndDownArrows();
}

const generateSectionWrapperFor = function(articleSectionElement, id) {
  const articleContent = document.querySelector('article.article-content');

  let sectionWrapper = document.createElement('div');
  sectionWrapper.id = 'article-section-wrapper-' + id;
  sectionWrapper.className = 'article-section-wrapper';
  sectionWrapper.dataset.sectionId = id;

  let trashImg = new Image();
  trashImg.className = 'img-svg';
  trashImg.title = global.get('articleSectionRemove');
  trashImg.alt = global.get('articleSectionRemove');
  trashImg.src = '/img/svg/trash.svg';

  let deleteButton = document.createElement('a');
  deleteButton.href = '#';
  deleteButton.id = 'article-section-button-delete-' + id;
  deleteButton.className = 'article-section-button article-section-button-delete';
  deleteButton.addEventListener('click', e => removeSection(e, id));
  deleteButton.appendChild(trashImg);

  let arrowUpImg = new Image();
  arrowUpImg.className = 'img-svg';
  arrowUpImg.title = global.get('articleSectionMoveUp');
  arrowUpImg.alt = global.get('articleSectionMoveUp');
  arrowUpImg.src = '/img/svg/arrow-fat-up.svg';

  let moveUpButton = document.createElement('a');
  moveUpButton.href = '#';
  moveUpButton.id = 'article-section-button-up-' + id;
  moveUpButton.className = 'article-section-button article-section-button-up';
  moveUpButton.addEventListener('click', e => moveSectionUp(e, id));
  moveUpButton.appendChild(arrowUpImg);

  let arrowDownImg = new Image();
  arrowDownImg.className = 'img-svg';
  arrowDownImg.title = global.get('articleSectionMoveDown');
  arrowDownImg.alt = global.get('articleSectionMoveDown');
  arrowDownImg.src = '/img/svg/arrow-fat-down.svg';

  let moveDownButton = document.createElement('a');
  moveDownButton.href = '#';
  moveDownButton.id = 'article-section-button-down-' + id;
  moveDownButton.className = 'article-section-button article-section-button-down';
  moveDownButton.addEventListener('click', e => moveSectionDown(e, id));
  moveDownButton.appendChild(arrowDownImg);

  let sectionControls = document.createElement('div');
  sectionControls.className = 'article-section-controls';

  sectionControls.appendChild(moveUpButton);
  sectionControls.appendChild(moveDownButton);
  sectionControls.appendChild(deleteButton);

  sectionWrapper.appendChild(articleSectionElement);
  sectionWrapper.appendChild(sectionControls);

  articleContent.appendChild(sectionWrapper);

  displayUpAndDownArrows();
};

const getSectionTypeIdFromClassList = function(classList) {
  if (classList.contains('article-section-paragraph')) {
    return 1;
  }

  if (classList.contains('article-section-image')) {
    return 2;
  }

  if (classList.contains('article-section-video')) {
    return 3;
  }

  if (classList.contains('article-section-title')) {
    return 4;
  }

  if (classList.contains('article-section-subtitle')) {
    return 5;
  }

  return 0;
};

const displayUpAndDownArrows = function() {
  let arrowDownAll = document.querySelectorAll('.article-section-button-down');
  let count = 0;

  arrowDownAll.forEach(d => {
    if (arrowDownAll.length && count !== arrowDownAll.length - 1) {
      d.classList.remove('null');
    } else {
      d.classList.add('null');
    }
    count++;
  });

  let arrowUpAll = document.querySelectorAll('.article-section-button-up');

  count = 0;
  arrowUpAll.forEach(u => {
    if (arrowUpAll.length && count !== 0) {
      u.classList.remove('null');
    } else {
      u.classList.add('null');
    }
    count++;
  });
};

const updateArticleUri = (e) => {
  const articleTitleText = e.target.innerText;
  const articleUriInput = document.querySelector('input[name="articleUri"]');
  const articleIdEl = document.querySelector('#form-article input[name="articleId"]');
  const cleanInput = cleanTextForUrl(articleTitleText);

  const payload = JSON.stringify({
    articleId: articleIdEl.value.trim() ? Number.parseInt(articleIdEl.value.trim()) : null,
    uri: cleanInput
  });

  xhr.post('/back/article/uri/', payload)
    .then(response => articleUriInput.value = response.uri);
}

document.querySelectorAll('.article-save-button').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const articleIdEl = document.querySelector('#form-article input[name="articleId"]');

    const afterApiCall = function(response, articleUri) {
      if (response.articleId) {
        articleIdEl.value = response.articleId;
        const lang = document.documentElement.lang
          ? document.documentElement.lang.toLowerCase().trim()
          : 'en';
        history.pushState(
          "",
          document.title,
          '/' + lang + '/backoffice/articles/' + response.articleId
        );
      }
      const previewExists = document.querySelector('.article-preview');
      if (!previewExists) {
        const previewLink = document.createElement('a');
        previewLink.href = '/' + articleUri + '?preview=true';
        previewLink.target = '_blank';
        previewLink.className = 'article-preview m-l-1';
        previewLink.textContent = global.get('globalPreview');
        document.querySelectorAll('.article-control-bar-buttons').forEach(b => b.appendChild(previewLink));
      }
      document.querySelectorAll('.control-bar-creation').forEach(a => a.classList.remove('hidden'));
      document.querySelectorAll('span.article-updated-at').forEach(s => {
        s.textContent = getUpdatedAtTime();
      });
      document.querySelectorAll('span.article-created-at').forEach(s => {
        if (!s.textContent.trim().length) {
          s.textContent = getUpdatedAtTime();
        }
      });

      document.querySelectorAll('#side-options').forEach(i => i.classList.add('null'));
      document.querySelectorAll('.article-save-button').forEach(b => b.value = global.get('globalUpdate'));
      document.querySelectorAll('.article-preview').forEach(b => {
          b.href = response.uri + '?preview=true'
        });
    };

    const titleEl = document.querySelector('#article-title-main');
    const uriEl = document.querySelector('input[name="articleUri"]');
    const status = document.querySelector('.dropdown-menu-option[data-checked="1"]');
    const statusId = Number.parseInt(status.dataset.articleStatusId);
    const typeEl = document.querySelector('select[name="typeId"] option:checked');
    const articleTypeId = typeEl && typeEl.value ? Number.parseInt(typeEl.value) : null;
    const articleUri = uriEl && uriEl.value ? uriEl.value : null;

    document.querySelectorAll('.article-saving').forEach(ar => ar.classList.remove('null'));

    let tags = [];
    let sections = [];
    let articleContentHtml = '';
    let order = 1;
    document.querySelectorAll('.article-section').forEach(sec => {
      let section = sec.cloneNode(true);
      let editorId = section.dataset.sectionId ?? section.dataset.editorId;

      let sectionElement = section.classList.contains('article-section-paragraph')
        ? document.querySelector('#article-section-paragraph-' + editorId + '-html')
        : section;

      let sectionContentHtml = sectionElement.innerHTML.trim();

      for (let i = 0; i < sectionElement.children.length; i++) {
        let c = sectionElement.children[i];

        // If it's the first title section add the article link
        if (c.id === 'article-title-main' && articleUri) {
          let aEl = document.createElement('a');
          aEl.href = '/' + articleUri;
          aEl.target = '_blank';
          aEl.className = 'link-title';
          aEl.innerHTML = c.innerHTML.trim();
          let h1El = sectionElement.querySelector('h1');
          h1El.textContent = '';
          h1El.appendChild(aEl);
        }

        // Remove attributes and not required classes
        c.classList.remove('placeholder');
        c.removeAttribute('id');
        c.removeAttribute('contenteditable');
        delete c.dataset.placeholder;
        delete c.dataset.imageId;
      }

      articleContentHtml += sectionElement.innerHTML.trim();
      let currentSection = {
        id: section.dataset.sectionId ? Number.parseInt(section.dataset.sectionId) : null,
        sectionTypeId: getSectionTypeIdFromClassList(section.classList),
        contentHtml: sectionContentHtml,
        order: order++
      };

      if (section.classList.contains('article-section-image')) {
        const imageCaption = section.getElementsByClassName('article-section-image-caption');
        currentSection.imageCaption = imageCaption.length > 0 ? imageCaption[0].textContent : null;
        const image = section.getElementsByClassName('article-image');
        currentSection.imageId = image[0] ? Number.parseInt(image[0].dataset.imageId) : null;
      }

      sections.push(currentSection);
    });
    document.querySelectorAll('#tags-selected > .result-selected')
      .forEach(t => {
        tags.push({
            id: t.dataset.tagId ? Number.parseInt(t.dataset.tagId) : null,
            name: t.textContent.trim()
          });
      });

    if (!articleContentHtml || !articleContentHtml.trim().length) {
      feedbackDiv.textContent = global.get('feedbackSaving');
      feedbackDiv.classList.remove('feedback-error');
      feedbackDiv.classList.add('feedback-success');
      feedbackDiv.classList.remove('null');
      setTimeout(() => {feedbackDiv.classList.add('null')}, 5000);
      document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
      return;
    }

    const payload = JSON.stringify({
      'title': titleEl ? titleEl.textContent : null,
      'uri': articleUri,
      'contentHtml': articleContentHtml.trim().length ? articleContentHtml.trim() : null,
      'typeId': articleTypeId,
      'statusId': statusId,
      'sections': sections,
      'tags': tags,
      'publishOn': null
    });

    const url = '/back/article';

    if (articleIdEl && articleIdEl.value) {
      xhr.put(url + '/' + articleIdEl.value, payload, feedbackDiv, global.get('globalUpdated'))
        .then((response) => afterApiCall(response, uriEl.value))
        .finally(() => {
          document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
        });
    } else {
      xhr.post(url, payload, feedbackDiv, global.get('globalSaved'))
        .then((response) => afterApiCall(response, uriEl.value))
        .finally(() => {
          document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
        });
    }
  });
});

const addMouseListenerToImageInImagesSection = function (imageEl) {
  let currentImgId = imageEl.dataset.imageId;

  imageEl.onmouseover = function () {
    document.querySelector('#image-options-' + currentImgId).classList.remove('null');
  }

  imageEl.onmouseout = function () {
    document.querySelector('#image-options-' + currentImgId).classList.add('null');
  }
}

let sectionAllImages = document.querySelectorAll('.image-item');
if (sectionAllImages) {
  sectionAllImages.forEach(addMouseListenerToImageInImagesSection);
}

const deleteImage = async function (e, aEl) {
  e.preventDefault();

  const delRes = confirm(global.get('feedbackDeleteImageConfirmation'));
  if (!delRes) {
    return;
  }

  const imageId = Number.parseInt(aEl.parentNode.parentNode.dataset.imageId);

  xhr.delete('/api/image/' + imageId, feedbackDiv, global.get('feedbackImageDeleted'))
    .then(() => {
      const iDiv = document.querySelector(".image-item[data-image-id='" + imageId + "']");
      iDiv.classList.add('null');
    });
}
document.querySelectorAll('.image-delete').forEach(function (aEl) {
  aEl.addEventListener('click', e => {
    deleteImage(e, aEl).then();
  });
});

const inputFileImages = document.querySelector('input[name="images"]');
if (inputFileImages) {
  inputFileImages.addEventListener('change', e => {
    e.preventDefault();

    const articleImagesDiv = document.querySelector('#images-list');
    const files = inputFileImages.files;
    const articleIdInput = document.querySelector('#form-article input[name="articleId"]');
    const eventIdInput = document.querySelector('form#invitation input[name="eventId"]');
    const eventId = eventIdInput ? eventIdInput.value : null;

    for (let i = 0; i < files.length; i++) {
      let formData = new FormData();
      let file = files[i]

      if (!/\.(jpe?g|png|gif|webp)$/i.test(file.name)) {
        return alert(file.name + " is not an image");
      }

      formData.append('files[]', file);
      formData.append('articleId', articleIdInput ? Number.parseInt(articleIdInput.value) : null);
      formData.append('eventId', eventId);

      const reader = new FileReader();
      reader.addEventListener('load', function () {
        let articleImageDiv = document.createElement('div');
        articleImageDiv.className = 'image-item';
        let image = new Image();
        image.className = 'opacity preview';
        image.alt = file.name;
        image.src = String(reader.result);
        let imgLoading = new Image();
        imgLoading.className = 'justify-center';
        imgLoading.alt = global.get('globalLoading');
        imgLoading.src = '/img/loading.gif';

        articleImageDiv.appendChild(image);
        articleImageDiv.appendChild(imgLoading);
        articleImagesDiv.appendChild(articleImageDiv);

        fetch(
          '/api/image',
          {
            method: 'POST',
            body: formData
          }
        ).then(response => {
          if (!response.ok) {
            throw new Error(response.status + ': ' + response.statusText);
          }

          return response.json();
        })
          .then(data => {
            if (!data.success || !data.images || data.images.length <= 0) {
              throw new Error(
                data.errorMessage ?? global.get('genericError') + ': ' + image.src
              );
            }

            data.images.forEach((i) => {
              let imageId = i.id;

              let imageDeleteDiv = document.createElement('div');
              imageDeleteDiv.id = 'image-options-' + imageId;
              imageDeleteDiv.className = 'options null';
              let imageDeleteA = document.createElement('a');
              imageDeleteA.href = '#';
              imageDeleteA.className = 'image-delete';
              imageDeleteA.innerHTML = '&#10006;';
              imageDeleteDiv.appendChild(imageDeleteA);
              articleImageDiv.appendChild(imageDeleteDiv);

              image.classList.remove('opacity');
              image.src = i.url;
              articleImageDiv.dataset.imageId = imageId;
              image.dataset.imageId = imageId;
              image.dataset.eventId = eventId;
              imgLoading.classList.add('null');
              addMouseListenerToImageInImagesSection(articleImageDiv);
              imageDeleteA.addEventListener('click', e => {
                deleteImage(e, imageDeleteA).then();
              });
            });
          }).catch((error) => {
          articleImageDiv.classList.add('null');
          feedbackDiv.textContent = global.get('genericError');
          feedbackDiv.classList.remove('feedback-success');
          feedbackDiv.classList.add('feedback-error');
          feedbackDiv.classList.remove('null');
          setTimeout(() => {
            feedbackDiv.classList.add('null')
          }, 5000);
        });
      });
      reader.readAsDataURL(file);
    }
  });
}

const formUser = document.querySelector('form#form-user');
if (formUser) {
  formUser.addEventListener('submit', e => {
    e.preventDefault();

    const userIdEl = document.querySelector('input#userId');
    const nameEl = document.querySelector('input#name');
    const emailEl = document.querySelector('input#email');
    const bioEl = document.querySelector('textarea#bio');
    const languageIdEl = document.querySelector('select#languageId');
    const roleIdEl = document.querySelector('select#roleId');
    const timezoneEl = document.querySelector('select#timezone');
    const isEnabledEl = document.querySelector('.dropdown-menu-option[data-checked="1"]');
    const isEnabled = Number.parseInt(isEnabledEl.dataset.value) > 0;

    const userId = userIdEl && userIdEl.value ? Number.parseInt(userIdEl.value) : null;

    const payload = JSON.stringify({
      'name': nameEl.value ?? null,
      'email': emailEl.value ?? null,
      'bio': bioEl.value ?? null,
      'languageId': languageIdEl.value ?? null,
      'roleId': roleIdEl.value ?? null,
      'timezone': timezoneEl.value ?? null,
      'isEnabled': isEnabled
    });

    if (userId) {
      xhr.put('/back/user/' + userId, payload, feedbackDiv)
        .then(() => window.location = '/backoffice/users');
    } else {
      xhr.post('/back/user', payload, feedbackDiv)
        .then(() => window.location = '/backoffice/users');
    }
  });
}

document.querySelectorAll('.article-status-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('.dropdown-menu-label').forEach(d => {
      if (op.dataset.articleStatusId === '1') {
        d.classList.add('feedback-success');
        d.classList.remove('background-light-color');
      } else {
        d.classList.remove('feedback-success');
        d.classList.add('background-light-color');
      }
    });

    document.querySelectorAll('.dropdown-menu-label > span').forEach(sp => {
      sp.textContent = op.textContent;
    });

    document.querySelectorAll('.dropdown-menu').forEach(d => {
      d.checked = false;
    });

    document.querySelectorAll('.article-status-option').forEach(o => {
      o.dataset.checked = o.dataset.articleStatusId === op.dataset.articleStatusId
        ? '1'
        : '0';
    });
  });
});

document.querySelectorAll('.user-enabled-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('.dropdown-menu-label').forEach(d => {
      if (op.dataset.value === '1') {
        d.classList.add('feedback-success');
        d.classList.remove('background-light-color');
      } else {
        d.classList.remove('feedback-success');
        d.classList.add('background-light-color');
      }
    });

    document.querySelectorAll('.dropdown-menu-label > span').forEach(sp => {
      sp.textContent = op.textContent;
    });

    document.querySelectorAll('.dropdown-menu').forEach(d => {
      d.checked = false;
    });

    document.querySelectorAll('.user-enabled-option').forEach(o => {
      o.dataset.checked = o.dataset.value === op.dataset.value
        ? '1'
        : '0';
    });
  });
});

document.querySelectorAll('.article-add-section-paragraph').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    const existingSections = document.querySelectorAll('section.article-section');
    if (existingSections.length &&
      existingSections[existingSections.length - 1].classList.contains('article-section-paragraph')
    ) {
      let id = existingSections[existingSections.length - 1].dataset.sectionId
        ?? existingSections[existingSections.length - 1].dataset.editorId;
      document.querySelector('.article-section-paragraph-' + id).focus();
      return;
    }

    const articleContentHtml = document.querySelector('div.article-content-text');
    const id = generateRandomString(5);
    const sectionId = 'article-section-paragraph-' + id;

    let articleSectionTextHtml = document.createElement('div');
    articleSectionTextHtml.id = sectionId + '-html';

    let articleSectionText = document.createElement('section');
    articleSectionText.id = sectionId;
    articleSectionText.dataset.editorId = id;
    articleSectionText.className = 'article-section article-content article-section-paragraph placeholder';
    articleSectionText.dataset.placeholder = global.get('editorParagraphPlaceholder');

    let divEditor = document.createElement('div');
    divEditor.className = 'pell-content ' + sectionId;
    divEditor.contentEditable = 'true';
    articleSectionText.appendChild(divEditor);

    generateSectionWrapperFor(articleSectionText, id);
    articleContentHtml.appendChild(articleSectionTextHtml);

    loadEditor(sectionId);

    document.querySelector('.' + sectionId).focus();
  });
});

document.querySelectorAll('.article-add-section-title').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    const id = generateRandomString(5);
    const sectionId = 'article-section-title-' + id;

    let articleSectionWrapper = document.createElement('section');
    articleSectionWrapper.id = sectionId;
    articleSectionWrapper.className = 'article-section article-section-title';

    let articleSectionTitle = document.createElement('h1');
    articleSectionTitle.className = 'article-title placeholder';
    articleSectionTitle.dataset.placeholder = global.get('editorTitlePlaceholder');
    articleSectionTitle.contentEditable = 'true';

    articleSectionWrapper.appendChild(articleSectionTitle);
    generateSectionWrapperFor(articleSectionWrapper, id);

    const allTitles = document.querySelectorAll('h1.article-title');
    if (allTitles.length === 1) {
      articleSectionTitle.id = 'article-title-main';
      allTitles[0].addEventListener('input', (e) => updateArticleUri(e));
    }

    document.querySelector('#' + sectionId + ' > h1').focus();
  });
});

document.querySelectorAll('.article-add-section-subtitle').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    const id = generateRandomString(5);
    const sectionId = 'article-section-subtitle-' + id;

    let articleSectionWrapper = document.createElement('section');
    articleSectionWrapper.id = sectionId;
    articleSectionWrapper.className = 'article-section article-section-subtitle';

    let articleSectionSubtitle = document.createElement('h2');
    articleSectionSubtitle.className = 'article-subtitle placeholder';
    articleSectionSubtitle.dataset.placeholder = global.get('editorSubtitlePlaceholder');
    articleSectionSubtitle.contentEditable = 'true';

    articleSectionWrapper.appendChild(articleSectionSubtitle);
    generateSectionWrapperFor(articleSectionWrapper, id);

    document.querySelector('#' + sectionId).focus();
  });
});

document.querySelectorAll('.article-add-section-video').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    // ToDo: Implement popup to get the video URL
    const videoUrl = window.prompt(global.get('editorVideoUrlTitle'));
    if (videoUrl) {
      const ytVideoId = getYoutubeVideoIdFromUrl(videoUrl);
      if (!ytVideoId) {
        return;
      }

      let articleSectionVideo = document.createElement('section');
      articleSectionVideo.className = 'article-section article-section-video';
      let iframeWrapper = document.createElement('div');
      iframeWrapper.className = 'article-section-video';
      let articleSectionIframe = document.createElement('iframe');
      articleSectionIframe.width = '560';
      articleSectionIframe.height = '315';
      articleSectionIframe.src = 'https://www.youtube-nocookie.com/embed/' + ytVideoId;
      articleSectionIframe.frameBorder = '0';
      articleSectionIframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      articleSectionIframe.allowFullscreen = true;

      iframeWrapper.appendChild(articleSectionIframe);
      articleSectionVideo.appendChild(iframeWrapper);

      generateSectionWrapperFor(articleSectionVideo, generateRandomString(5));
    }
  });
});

document.querySelectorAll('.article-add-section-html').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    alert('ToDo');
  });
});

const inputArticleImages = document.querySelector('input[name="article-add-image-input"]');
if (inputArticleImages) {
  inputArticleImages.addEventListener('change', e => {
    e.preventDefault();

    const articleContentDiv = document.querySelector('article.article-content');
    const files = inputArticleImages.files;
    const articleIdInput = document.querySelector('#form-article input[name="articleId"]');

    for (let i = 0; i < files.length; i++) {
      let formData = new FormData();
      let file = files[i]

      if (!/\.(jpe?g|png|gif|webp)$/i.test(file.name)) {
        return alert(file.name + ' ' + global.get('feedbackErrorNotAnImage'));
      }

      formData.append('files[]', file);
      formData.append('articleId', articleIdInput ? Number.parseInt(articleIdInput.value) : null);

      const reader = new FileReader();
      reader.addEventListener('load', function () {
        let id = generateRandomString(5);

        let articleSectionImage = document.createElement('section');
        articleSectionImage.className = 'article-section article-section-image';

        let imageCaption = document.createElement('p');
        imageCaption.className = 'placeholder article-section-image-caption';
        imageCaption.dataset.placeholder = global.get('editorTitlePlaceholder');
        imageCaption.contentEditable = 'true';

        let image = new Image();
        image.className = 'opacity article-image';
        image.src = String(reader.result);

        let imgLoading = new Image();
        imgLoading.className = 'justify-center';
        imgLoading.alt = global.get('globalLoading');
        imgLoading.src = '/img/loading.gif';

        articleSectionImage.appendChild(image);
        articleSectionImage.appendChild(imgLoading);
        articleSectionImage.appendChild(imageCaption);

        generateSectionWrapperFor(articleSectionImage, id);

        const pPlaceholders = document.querySelectorAll('.placeholder');
        pPlaceholders[pPlaceholders.length - 1]
          .addEventListener('focus', managePlaceholderForEditableElements);

        fetch(
          '/api/image',
          {
            method: 'POST',
            body: formData
          }
        ).then(response => {
          if (!response.ok) {
            throw new Error(response.status + ': ' + response.statusText);
          }

          try {
            return response.json();
          } catch (error) {
            throw new Error(error.message);
          }
        })
          .then(data => {
            if (!data.success || !data.images || data.images.length <= 0) {
              throw new Error(
                data.errorMessage ?? global.get('genericError') + ': ' + image.src
              );
            }

            data.images.forEach((i) => {
              image.classList.remove('opacity');
              image.src = i.url;
              image.dataset.imageId = i.id;
              image.alt = i.caption ?? i.url;
              articleSectionImage.removeChild(imgLoading);
            });
          }).catch((error) => {
            articleContentDiv.removeChild(articleSectionImage);
            feedbackDiv.textContent = error.message;
            feedbackDiv.classList.remove('feedback-success');
            feedbackDiv.classList.add('feedback-error');
            feedbackDiv.classList.remove('null');
            setTimeout(() => {
              feedbackDiv.classList.add('null')
            }, 5000);
          });
      });
      reader.readAsDataURL(file);
    }
  });
}

document.querySelectorAll('.placeholder').forEach(el => {
  el.addEventListener('focus', managePlaceholderForEditableElements);
})

document.querySelectorAll('.article-section-button-delete').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const sectionId = Number.parseInt(el.parentNode.parentNode.dataset.sectionId);
    removeSection(e, sectionId);
  });
});

document.querySelectorAll('.article-section-button-up').forEach(el => {
  el.addEventListener('click', e => moveSectionUp(e, el.parentNode.parentNode.dataset.sectionId));
});

document.querySelectorAll('.article-section-button-down').forEach(el => {
  el.addEventListener('click', e => moveSectionDown(e, el.parentNode.parentNode.dataset.sectionId));
});

document.querySelectorAll('section.article-section-paragraph').forEach(s => loadEditor(s.id));

document.querySelectorAll('h1#article-title-main').forEach(t => {
  t.addEventListener('input', (e) => updateArticleUri(e))
});

document.querySelectorAll('a.article-settings').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('input[name="tags"]').forEach(i => i.value = '');

    const sideNav = document.getElementById('side-options');
    sideNav.classList.remove('null');
    sideNav.classList.add('side-options-open');
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
};

const handleSearchResultClick = function(event) {
  event.preventDefault();

  const tags = document.querySelector('#tags-selected');
  const el = event.target;

  document.querySelector('#search-results-tags').classList.add('null');

  let existingTagIds = [];
  document.querySelectorAll('#tags-selected > .result-selected')
    .forEach(s => {
      if (el.dataset.tagId === s.dataset.tagId) {
        s.classList.add('highlight-effect');
        setTimeout(() => s.classList.remove('highlight-effect'), 2000);
      }
      existingTagIds.push(s.dataset.tagId)
    });

  if (existingTagIds.includes(el.dataset.tagId)) {
    return;
  }

  let imgClose = new Image();
  imgClose.className = 'img-svg m-l-05';
  imgClose.title = global.get('globalRemove');
  imgClose.alt = global.get('globalRemove');
  imgClose.src = '/img/svg/x.svg';
  imgClose.addEventListener('click', (e) => handleRemoveArticleTag(e));

  let newTag = document.createElement('span');
  newTag.className = 'result-selected';
  newTag.dataset.tagId = el.dataset.tagId;
  newTag.textContent = el.textContent;
  newTag.appendChild(imgClose);
  tags.appendChild(newTag);
};

document.querySelectorAll('input[name="tags"]').forEach(el => {
  const searchResultEl = document.querySelector('#search-results-tags');

  el.addEventListener('input', (e) => {
    e.preventDefault();

    searchResultEl.querySelectorAll('span.result-item').forEach(c => searchResultEl.removeChild(c));
    let inputText = e.target.value.trim();
    if (inputText.length <= 1) {
      searchResultEl.classList.add('null');
      return;
    }

    xhr.get('/back/tag?name=' + inputText)
      .then(response => {
        searchResultEl.classList.remove('null');
        response.tags.forEach(tag => {
          let newTag = document.createElement('span');
          newTag.className = 'result-item';
          newTag.dataset.tagId = tag.id;
          newTag.textContent = tag.name;
          searchResultEl.appendChild(newTag);
        });

        document.querySelectorAll('span.result-item').forEach(r => {
          r.addEventListener('click', (e) => handleSearchResultClick(e));
        });
      });
  });

  el.addEventListener('focus', (e) => {
    let inputText = e.target.value.trim();
    if (inputText.length && document.querySelectorAll('span.result-item').length) {
      searchResultEl.classList.remove('null');
    }
  });
});

document.querySelectorAll('a.search-results-close').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelector('#search-results-tags').classList.add('null');
  });
});
