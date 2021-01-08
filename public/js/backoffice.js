import {cleanTextForUrl, getUpdatedAtTime} from './util.js';
import {xhr} from './xhr.js';
import {feedbackDiv} from './authorised.js';

document.querySelectorAll('#form-article').forEach(el => {
  el.addEventListener('submit', e => {
    e.preventDefault();

    const afterApiCall = function() {
      if (e.submitter.dataset.close) {
        window.location = '/backoffice/articles'
      } else {
        document.querySelectorAll('span.articleUpdatedAt').forEach(s => {
          s.textContent = getUpdatedAtTime(s.dataset.lang);
        });
      }
    };

    const articleIdEl = document.querySelector('#form-article input[name="articleId"]');
    const title = document.querySelector('div#article-title');
    const uri = document.querySelector('#form-article input[name="uri"]');
    const content = document.querySelector('div#content-html');
    const status = document.querySelector('#form-article select[name="statusId"]');
    const statusId = Number.parseInt(status.options[status.selectedIndex].value);
    const type = document.querySelector('#form-article select[name="typeId"]');
    const typeId = Number.parseInt(type.options[type.selectedIndex].value);

    const payload = JSON.stringify({
      'title': title.textContent,
      'uri': uri.value,
      'content': content.textContent,
      'statusId': statusId,
      'typeId': typeId
    });

    const url = '/back/article';

    if (articleIdEl) {
      xhr.put(url + '/' + articleIdEl.value, payload, feedbackDiv)
        .then(afterApiCall);
    } else {
      xhr.post(url, payload, feedbackDiv)
        .then(afterApiCall);
    }
  });
});

const articleTitleInput = document.querySelector('div#article-title');
if (articleTitleInput) {
  const articleUriInput = document.querySelector('#form-article input[name="uri"]');
  articleTitleInput.addEventListener('input', () => {
    articleUriInput.value = cleanTextForUrl(articleTitleInput.innerText);

    if (articleTitleInput.innerText.trim().length > 0) {
      articleTitleInput.classList.add('input-div-clean');
    } else {
      articleTitleInput.classList.remove('input-div-clean');
    }
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

    const userFeedback = document.querySelector('#feedback');
    const articleImagesDiv = document.querySelector('#images-list');
    const files = document.querySelector('[type=file]').files;
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
        imgLoading.className = 'centered';
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
        ).then(response => response.json())
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
          userFeedback.textContent = 'Something went wrong, please try again';
          userFeedback.classList.remove('feedback-success');
          userFeedback.classList.add('feedback-failure');
          userFeedback.classList.remove('null');
          setTimeout(() => {
            userFeedback.classList.add('null')
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
        toggleEnableUser.classList.toggle('enabled-icon-yes');
        toggleEnableUser.classList.toggle('enabled-icon-no');
        toggleEnableUser.dataset.enabled = enabled ? '1' : '';
        toggleEnableUser.textContent = enabled ? 'Enabled' : 'Disabled';
      });
  });
}
