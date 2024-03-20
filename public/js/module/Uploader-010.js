import {Request} from './Request-002.js';
import {Global} from './localisation-004.js';
import {Util} from "./Util-009.js";

class UploaderClass {
  static buildImageLoadingElement() {
    let container = document.createElement('div');
    container.className = 'loader-container';

    let loaderDiv = document.createElement('div');
    loaderDiv.className = 'loader';
    container.appendChild(loaderDiv);

    return container;
  }

  static buildProgressElement() {
    let loaderContainer = document.createElement('div');
    loaderContainer.className = 'loader-container';

    let progressEl = document.createElement('progress');
    progressEl.max = 100;
    progressEl.value = 0;
    loaderContainer.appendChild(progressEl);

    return loaderContainer;
  }

  async uploadMedia (
    media,
    apiUploadEndpoint = '/api/file',
    formData = new FormData(),
  ) {
    if (!/\.(jpe?g|png|gif|webp)$/i.test(media.name)) {
      return new Promise((resolve, reject) => reject(new Error(media.name + ' is not an image')));
    }

    return new Promise((resolve, reject) => {
      const figureContainer = document.querySelector('#img' + Util.cleanString(media.name));
      const progressBarContainer = UploaderClass.buildProgressElement();
      const progressEl = progressBarContainer.querySelector('progress');
      figureContainer.appendChild(progressBarContainer);
      figureContainer.removeChild(figureContainer.querySelector('.loader-container'));

      formData.append('files[]', media);

      let xhr = new XMLHttpRequest()
      xhr.onload = () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
          resolve(JSON.parse(xhr.response));
        } else {
          reject(new Error(xhr.statusText));
        }
      }

      xhr.upload.addEventListener('progress', (e) => {
        progressEl.value = Math.round((e.loaded / e.total) * 100);
      });

      xhr.open('POST', apiUploadEndpoint);
      xhr.send(formData);
    });
  }

  async uploadMediaAsync(
    files,
    mediaContainer,
    then = () => {},
    catchError = () => {},
    imageClassName = 'image-item',
    apiUploadEndpoint = '/api/file',
    formData = new FormData(),
  ) {
    for (const media of Array.from(files)) {
      const loaderEl = UploaderClass.buildImageLoadingElement();
      const figureContainer = document.createElement('figure');
      figureContainer.className = 'image-container media-uploading';
      figureContainer.id = 'img' + Util.cleanString(media.name);

      figureContainer.appendChild(loaderEl);

      mediaContainer.appendChild(figureContainer);
      mediaContainer.firstChild
        ? mediaContainer.insertBefore(figureContainer, mediaContainer.firstChild)
        : mediaContainer.appendChild(figureContainer);
    }

    mediaContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

    for (const media of Array.from(files)) {
      try {
        await this.uploadMedia(media, apiUploadEndpoint, formData)
          .then(json => {
            if (!json.success || !json.file) {
              throw new Error(json.errorMessage ?? Global.get('genericError'));
            }

            const figureContainer = document.querySelector('#img' + Util.cleanString(json.file.sourceName));

            const media = new Image();
            media.className = imageClassName;
            media.src = json.file.pathSmall;
            media.sizes = json.file.sizes;
            media.srcset = json.file.srcset;
            media.dataset.mediaId = json.file.id;
            media.dataset.pathMedium = json.file.pathMedium;
            media.alt = json.file.caption ?? json.file.name;

            figureContainer.appendChild(media);
            figureContainer.removeChild(figureContainer.querySelector('.loader-container'));
            figureContainer.classList.remove('media-uploading');
            figureContainer.removeAttribute('id');

            then(json);
          })
          .catch(error => {
            const figureContainer = document.querySelector('#img' + Util.cleanString(media.name));
            if (figureContainer) {
              mediaContainer.removeChild(figureContainer);
            }

            Util.notifyError(error);
          });
      } catch (error) {
        Util.notifyError(error);
      }
    }
  }

  // Deprecated
  async uploadImage(
    file,
    imageContainer,
    then = () => {},
    catchError = () => {},
    imageClassName = 'image-item',
    apiUploadEndpoint = '/api/file',
    formData = new FormData(),
  ) {
    if (!/\.(jpe?g|png|gif|webp)$/i.test(file.name)) {
      Util.notifyError(new Error(file.name + ' is not an image'));
      return;
    }

    formData.append('files[]', file);

    const reader = new FileReader();
    reader.addEventListener('load', function () {
      let image = new Image();
      image.className = 'opacity' + (imageClassName ? ' ' + imageClassName : '');
      // image.src = String(reader.result);

      const imgLoading = UploaderClass.buildImageLoadingElement();
      const figureContainer = document.createElement('figure');
      figureContainer.className = 'image-container';

      figureContainer.appendChild(image);
      figureContainer.appendChild(imgLoading);

      imageContainer.appendChild(figureContainer);
      imageContainer.firstChild
        ? imageContainer.insertBefore(figureContainer, imageContainer.firstChild)
        : imageContainer.appendChild(figureContainer);

      Request.postImage(apiUploadEndpoint, formData)
        .then(response => {
          if (!response.success || !response.file) {
            throw new Error(response.errorMessage ?? Global.get('genericError'));
          }

          image.classList.remove('opacity');
          image.src = response.file.pathSmall;
          image.sizes = response.file.sizes;
          image.srcset = response.file.srcset;
          image.dataset.mediaId = response.file.id;
          image.dataset.pathMedium = response.file.pathMedium;
          image.alt = response.file.caption ?? response.file.name;
          figureContainer.removeChild(imgLoading);

          then(response);
        })
        .catch((error) => {
          try {
            imageContainer.removeChild(figureContainer);
          } catch {}

          Util.notifyError(error);
          catchError();
        });
    })

    reader.readAsDataURL(file);
  };

  uploadFile(
    file,
    container,
    fileClassName,
    then = () => {},
    catchError = () => {},
    apiUploadEndpoint = '/api/file',
    formData = new FormData(),
  ) {
    formData.append('files[]', file);

    const reader = new FileReader();
    reader.addEventListener('load', function () {
      let fileNameSpan = document.createElement('span');
      fileNameSpan.textContent = file.name;
      const imgLoading = UploaderClass.buildImageLoadingElement();

      container.appendChild(fileNameSpan);
      container.appendChild(imgLoading);

      Request.postImage(apiUploadEndpoint, formData)
        .then(data => {
          if (!data.success) {
            throw new Error(data.errorMessage ?? Global.get('genericError'));
          }

          container.removeChild(fileNameSpan);
          container.removeChild(imgLoading);

          then(data);
        })
        .catch((error) => {
          container.removeChild(fileNameSpan);
          container.removeChild(imgLoading);

          Util.notifyError(error);
          catchError();
        });
    });

    reader.readAsDataURL(file);
  }
}

export const Uploader = new UploaderClass();
