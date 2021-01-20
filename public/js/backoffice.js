import {
  cleanTextForUrl,
  getUpdatedAtTime,
  getYoutubeVideoIdFromUrl,
  managePlaceholderForEditableElements,
  generateRandomString
} from './util.js';
import {xhr} from './xhr.js';
import {feedbackDiv} from './authorised.js';

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
        const controlBarButtons = document.querySelector('.article-control-bar-buttons');
        const previewLink = document.createElement('a');
        previewLink.href = '/' + articleUri + '?preview=true';
        previewLink.target = '_blank';
        previewLink.className = 'm-l-1';
        previewLink.textContent = 'Preview';
        controlBarButtons.appendChild(previewLink);
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
    document.querySelectorAll('section.article-section').forEach(section => {
      const sectionTypeId = section.classList.contains('article-section-image')
        ? 2
        : section.classList.contains('article-section-text') ? 1 : 3;

      // Hacky - ToDo: Move remove link outside section content
      if (sectionTypeId === 2) {
        section.removeChild(section.lastElementChild);
      }

      let sectionContent = section.innerHTML.trim();

      // Hacky but it does de work for now
      sectionContent = sectionContent.replace('contenteditable="true"', '');

      html += sectionContent;
      let currentSection = {
        id: section.dataset.sectionId ? Number.parseInt(section.dataset.sectionId) : null,
        sectionTypeId: sectionTypeId,
        contentHtml: sectionContent,
        order: order++
      };

      if (sectionTypeId === 2) {
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
  articleTitleInput.addEventListener('input', () => {
    const cleanInput = cleanTextForUrl(articleTitleInput.innerText);

    const payload = JSON.stringify({
      uri: cleanInput
    });

    xhr.post('/back/article/uri/', payload)
      .then(response => {
        articleUriInput.value = response.uri;

        articleTitleInput.innerText.trim().length > 0
          ? articleTitleInput.classList.add('input-div-clean')
          : articleTitleInput.classList.remove('input-div-clean');
      });
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
        image.title = file.name;
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
                data.errorMessage ?? 'Something went wrong, please try again: ' + image.title
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

document.querySelectorAll('.article-add-section-text').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    const articleContentDiv = document.querySelector('article.article-content');
    const sectionId = 'article-section-text-' + generateRandomString(5);

    let articleSectionText = document.createElement('section');
    articleSectionText.id = sectionId + '-html';
    articleSectionText.className = 'article-section article-section-text placeholder null';
    articleSectionText.dataset.placeholder = 'Type something...';

    let divSection = document.createElement('div');
    divSection.id = sectionId;

    let divEditor = document.createElement('div');
    divEditor.className = 'pell-content ' + sectionId;
    divEditor.contentEditable = 'true';
    divSection.appendChild(divEditor);

    articleContentDiv.appendChild(divSection);
    articleContentDiv.appendChild(articleSectionText);

    loadEditor(sectionId);

    document.querySelector('.' + sectionId).focus();
  });
})

document.querySelectorAll('.article-add-section-video').forEach(bu => {
  bu.addEventListener('click', e => {
    e.preventDefault();

    const articleContentDiv = document.querySelector('article.article-content');

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
      articleContentDiv.appendChild(articleSectionVideo);
    }
  });
})

const removeImageFromArticle = function(e, imageId) {
  e.preventDefault();

  const delRes = confirm('Are you sure you want to delete this image?');
  if (!delRes) {
    return;
  }

  const article = document.querySelector('.article-content');
  document.querySelectorAll('.article-image').forEach(i => {
    if (Number.parseInt(i.dataset.imageId) === imageId) {
      article.removeChild(i.parentElement);
    }
  });
};

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
        let articleSectionImage = document.createElement('section');
        articleSectionImage.className = 'article-section article-section-image';

        let imageCaption = document.createElement('p');
        imageCaption.className = 'placeholder article-section-image-caption';
        imageCaption.dataset.placeholder = 'Type something...';
        imageCaption.contentEditable = 'true';

        let image = new Image();
        image.className = 'opacity article-image';
        image.title = file.name;
        image.src = String(reader.result);

        let imgLoading = new Image();
        imgLoading.className = 'justify-center';
        imgLoading.alt = 'Loading...';
        imgLoading.src = '/img/loading.gif';

        articleSectionImage.appendChild(image);
        articleSectionImage.appendChild(imgLoading);
        articleSectionImage.appendChild(imageCaption);
        articleContentDiv.appendChild(articleSectionImage);

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
                data.errorMessage ?? 'Something went wrong, please try again: ' + image.title
              );
            }

            data.images.forEach((i) => {
              let imageId = i.id;

              image.classList.remove('opacity');
              image.src = i.url;
              image.dataset.imageId = imageId;
              articleSectionImage.removeChild(imgLoading);

              let deleteButton = document.createElement('a');
              deleteButton.dataset.imageId = imageId;
              deleteButton.className = 'article-section-image-delete article-section-image-control-button';
              deleteButton.textContent = 'Remove from article';
              articleSectionImage.appendChild(deleteButton);

              document.querySelector(".article-section-image-delete[data-image-id='" + imageId + "']")
                .addEventListener('click', e => removeImageFromArticle(e, imageId));
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

document.querySelectorAll('.article-section-image-delete').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const imageId = Number.parseInt(el.dataset.imageId);
    removeImageFromArticle(e, imageId);
  });
});

document.querySelectorAll('.article-image-control-up').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const imageId = Number.parseInt(el.dataset.imageId);

    alert('UP: ToDo: ' + imageId);
  });
});

document.querySelectorAll('.article-image-control-down').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const imageId = Number.parseInt(el.dataset.imageId);
    alert('DOWN: ToDo: ' + imageId);
  });
});
