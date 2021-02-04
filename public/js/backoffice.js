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

const removeSection = function(e, sectionId) {
  e.preventDefault();

  const delRes = confirm('Are you sure you want to delete this section?');
  if (!delRes) {
    return;
  }

  const article = document.querySelector('.article-content');
  const section = document.querySelector('#article-section-wrapper-' + sectionId);

  if (article && section) {
    article.removeChild(section);
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
  trashImg.title = 'Remove from article';
  trashImg.alt = 'Remove from article';
  trashImg.src = '/img/svg/trash.svg';

  let deleteButton = document.createElement('a');
  deleteButton.href = '#';
  deleteButton.id = 'article-section-button-delete-' + id;
  deleteButton.className = 'article-section-button article-section-button-delete';
  deleteButton.appendChild(trashImg);

  let arrowUpImg = new Image();
  arrowUpImg.className = 'img-svg';
  arrowUpImg.title = 'Move Up';
  arrowUpImg.alt = 'Move Up';
  arrowUpImg.src = '/img/svg/arrow-fat-up.svg';

  let moveUpButton = document.createElement('a');
  moveUpButton.href = '#';
  moveUpButton.id = 'article-section-button-up-' + id;
  moveUpButton.className = 'article-section-button article-section-button-up';
  moveUpButton.appendChild(arrowUpImg);

  let arrowDownImg = new Image();
  arrowDownImg.className = 'img-svg';
  arrowDownImg.title = 'Move Down';
  arrowDownImg.alt = 'Move Down';
  arrowDownImg.src = '/img/svg/arrow-fat-down.svg';

  let moveDownButton = document.createElement('a');
  moveDownButton.href = '#';
  moveDownButton.id = 'article-section-button-down-' + id;
  moveDownButton.className = 'article-section-button article-section-button-down';
  moveDownButton.appendChild(arrowDownImg);

  let sectionControls = document.createElement('div');
  sectionControls.className = 'article-section-controls';

  sectionControls.appendChild(moveUpButton);
  sectionControls.appendChild(moveDownButton);
  sectionControls.appendChild(deleteButton);

  sectionWrapper.appendChild(articleSectionElement);
  sectionWrapper.appendChild(sectionControls);

  articleContent.appendChild(sectionWrapper);

  document.querySelector("#article-section-button-delete-" + id)
    .addEventListener('click', e => removeSection(e, id));
  document.querySelector("#article-section-button-down-" + id)
    .addEventListener('click', e => moveSectionDown(e, id));
  document.querySelector("#article-section-button-up-" + id)
    .addEventListener('click', e => moveSectionUp(e, id));

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

document.querySelectorAll('#form-article').forEach(el => {
  el.addEventListener('submit', e => {
    e.preventDefault();

    const articleIdEl = document.querySelector('#form-article input[name="articleId"]');

    const afterApiCall = function(response, articleUri) {
      if (e.submitter.dataset.close) {
        window.location = '/backoffice/articles'
      } else {
        if (response.articleId) {
          articleIdEl.value = response.articleId;
          history.pushState("", document.title, '/backoffice/articles/' + response.articleId);
        }
        const previewExists = document.querySelector('.article-preview');
        if (!previewExists) {
          const previewLink = document.createElement('a');
          previewLink.href = '/' + articleUri + '?preview=true';
          previewLink.target = '_blank';
          previewLink.className = 'article-preview m-l-1';
          previewLink.textContent = 'Preview';
          document.querySelectorAll('.article-control-bar-buttons').forEach(b => b.appendChild(previewLink));
        }
        document.querySelectorAll('.article-creation').forEach(a => a.classList.remove('hidden'));
        document.querySelectorAll('span.article-updated-at').forEach(s => {
          s.textContent = getUpdatedAtTime(s.dataset.lang);
        });
        document.querySelectorAll('span.article-created-at').forEach(s => {
          if (!s.textContent.trim().length) {
            s.textContent = getUpdatedAtTime(s.dataset.lang);
          }
        });

        document.querySelectorAll('input.article-save').forEach(b => b.value = 'Update');
        document.querySelectorAll('input.article-save-close').forEach(b => {
          b.value = 'U&C'
        });
        document.querySelectorAll('.article-preview').forEach(b => {
          b.href = response.uri + '?preview=true'
        });
      }
    };

    const title = document.querySelector('div#article-title');
    const uri = document.querySelector('#form-article input[name="uri"]');
    const status = document.querySelector('.dropdown-menu-option[data-checked="1"]');
    const statusId = Number.parseInt(status.dataset.articleStatusId);
    document.querySelectorAll('.article-saving').forEach(ar => ar.classList.remove('null'));

    let sections = [];
    let html = '';
    let order = 1;
    document.querySelectorAll('.article-section').forEach(sec => {
      let section = sec.cloneNode(true);
      let editorId = section.dataset.sectionId ?? section.dataset.editorId;

      let sectionContent = section.classList.contains('article-section-paragraph')
        ? document.querySelector('#article-section-paragraph-' + editorId + '-html').innerHTML.trim()
        : section.innerHTML.trim();

      // Hacky but it does the work for now
      sectionContent = sectionContent.replace('contenteditable="true"', '');

      html += sectionContent;
      let currentSection = {
        id: section.dataset.sectionId ? Number.parseInt(section.dataset.sectionId) : null,
        sectionTypeId: getSectionTypeIdFromClassList(section.classList),
        contentHtml: sectionContent,
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

    const payload = JSON.stringify({
      'title': title.textContent,
      'uri': uri.value,
      'content': html,
      'statusId': statusId,
      'sections': sections
    });

    const url = '/back/article';

    if (articleIdEl && articleIdEl.value) {
      xhr.put(url + '/' + articleIdEl.value, payload, feedbackDiv, 'Updated')
        .then((response) => afterApiCall(response, uri.value))
        .finally(() => {
          document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
        });
    } else {
      xhr.post(url, payload, feedbackDiv, 'Saved')
        .then((response) => afterApiCall(response, uri.value))
        .finally(() => {
          document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
        });
    }
  });
});

const articleTitleInput = document.querySelector('div#article-title');
if (articleTitleInput) {
  const articleUriInput = document.querySelector('#form-article input[name="uri"]');
  const articleIdEl = document.querySelector('#form-article input[name="articleId"]');
  articleTitleInput.addEventListener('input', () => {
    const cleanInput = cleanTextForUrl(articleTitleInput.innerText);

    const payload = JSON.stringify({
      articleId: articleIdEl.value.trim() ? Number.parseInt(articleIdEl.value.trim()) : null,
      uri: cleanInput
    });

    xhr.post('/back/article/uri/', payload)
      .then(response => articleUriInput.value = response.uri);
  });
}

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

  const delRes = confirm('Are you sure you want to delete this image?');
  if (!delRes) {
    return;
  }

  const imageId = Number.parseInt(aEl.parentNode.parentNode.dataset.imageId);

  xhr.delete('/api/image/' + imageId, feedbackDiv, 'Imaxe borrada')
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
        imgLoading.alt = 'Loading...';
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
                data.errorMessage ?? 'Something went wrong, please try again: ' + image.src
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
          feedbackDiv.textContent = 'Something went wrong, please try again';
          feedbackDiv.classList.remove('feedback-success');
          feedbackDiv.classList.add('feedback-error');
          feedbackDiv.classList.remove('null');
          setTimeout(() => {
            feedbackDiv.classList.add('null')
          }, 5000);
          console.log(error);
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
    const isEnabledEl = document.querySelector('div#isEnabled');

    const userId = userIdEl && userIdEl.value ? Number.parseInt(userIdEl.value) : null;

    const payload = JSON.stringify({
      'name': nameEl.value ?? null,
      'email': emailEl.value ?? null,
      'bio': bioEl.value ?? null,
      'languageId': languageIdEl.value ?? null,
      'roleId': roleIdEl.value ?? null,
      'timezone': timezoneEl.value ?? null,
      'isEnabled': !!isEnabledEl.dataset.enabled
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

const toggleEnableUser = document.querySelector('div#isEnabled');
if (toggleEnableUser) {
  const userIdEl = document.querySelector('input#userId');
  const userId = userIdEl && userIdEl.value ? Number.parseInt(userIdEl.value) : null;

  toggleEnableUser.addEventListener('click', () => {
    const enabled = !toggleEnableUser.dataset.enabled;

    const payload = JSON.stringify({
      'isEnabled': enabled
    });

    xhr.put('/back/user/' + userId, payload, feedbackDiv)
      .then(() => {
        toggleEnableUser.classList.toggle('feedback-success');
        toggleEnableUser.classList.toggle('feedback-error');
        toggleEnableUser.dataset.enabled = enabled ? '1' : '';
        toggleEnableUser.textContent = enabled ? 'Enabled' : 'Disabled';
      });
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
    articleSectionText.dataset.placeholder = 'Type something...';

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

    let articleSectionTitle = document.createElement('h1');
    articleSectionTitle.id = sectionId;
    articleSectionTitle.className = 'article-section article-title article-section-title placeholder';
    articleSectionTitle.dataset.placeholder = 'Title';
    articleSectionTitle.contentEditable = 'true';

    generateSectionWrapperFor(articleSectionTitle, id);

    document.querySelector('#' + sectionId).focus();
  });
});

document.querySelectorAll('.article-add-section-subtitle').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    const id = generateRandomString(5);
    const sectionId = 'article-section-subtitle-' + id;

    let articleSectionSubtitle = document.createElement('h2');
    articleSectionSubtitle.id = sectionId;
    articleSectionSubtitle.className = 'article-section article-subtitle article-section-subtitle placeholder';
    articleSectionSubtitle.dataset.placeholder = 'Subtitle';
    articleSectionSubtitle.contentEditable = 'true';

    generateSectionWrapperFor(articleSectionSubtitle, id);

    document.querySelector('#' + sectionId).focus();
  });
});

document.querySelectorAll('.article-add-section-video').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    // ToDo: Implement popup to get the video URL
    const videoUrl = window.prompt('Video URL? (Only YouTube for now)');
    if (videoUrl) {
      const ytVideoId = getYoutubeVideoIdFromUrl(videoUrl);
      if (!ytVideoId) {
        return;
      }

      let articleSectionVideo = document.createElement('section');
      articleSectionVideo.className = 'article-section article-section-video';
      let articleSectionIframe = document.createElement('iframe');
      articleSectionIframe.width = '560';
      articleSectionIframe.height = '315';
      articleSectionIframe.src = 'https://www.youtube-nocookie.com/embed/' + ytVideoId;
      articleSectionIframe.frameBorder = '0';
      articleSectionIframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      articleSectionIframe.allowFullscreen = true;

      articleSectionVideo.appendChild(articleSectionIframe);

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
        return alert(file.name + " is not an image");
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
        imageCaption.dataset.placeholder = 'Type something...';
        imageCaption.contentEditable = 'true';

        let image = new Image();
        image.className = 'opacity article-image';
        image.src = String(reader.result);

        let imgLoading = new Image();
        imgLoading.className = 'justify-center';
        imgLoading.alt = 'Loading...';
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
                data.errorMessage ?? 'Something went wrong, please try again. Image: ' + image.src
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
            console.log(error);
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

document.querySelectorAll('a.article-settings').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();

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
