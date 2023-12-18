import {Request} from './Request-002.js';
import {Global} from './localisation-004.js';
import {Util} from "./Util-008.js";

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
      const figureContainer = document.querySelector('#' + Util.cleanString(media.name));
      const progressEl = UploaderClass.buildProgressElement();
      figureContainer.appendChild(progressEl);
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
        progressEl.setAttribute('value', (e.loaded / e.total * 100).toString());
      })

      xhr.open('POST', apiUploadEndpoint);
      xhr.send(formData);
    });
  }

  async uploadMediaSequence(
    files,
    imageContainer,
    then = () => {},
    catchError = () => {},
    imageClassName = 'image-item',
  ) {
    for (const media of Array.from(files)) {
      const loaderEl = UploaderClass.buildImageLoadingElement();
      const figureContainer = document.createElement('figure');
      figureContainer.className = 'image-container media-uploading';
      figureContainer.id = Util.cleanString(media.name);

      figureContainer.appendChild(loaderEl);

      imageContainer.appendChild(figureContainer);
      imageContainer.firstChild
        ? imageContainer.insertBefore(figureContainer, imageContainer.firstChild)
        : imageContainer.appendChild(figureContainer);
    }

    for (const media of Array.from(files)) {
      try {
        await this.uploadMedia(media)
          .then(json => {
            if (!json.success || !json.file) {
              throw new Error(json.errorMessage ?? Global.get('genericError'));
            }

            const figureContainer = document.querySelector('#' + Util.cleanString(json.file.sourceName));

            const media = new Image();
            media.className = imageClassName;
            media.src = json.file.pathSmall;
            media.dataset.mediaId = json.file.id;
            media.dataset.pathMedium = json.file.pathMedium;
            media.alt = json.file.caption ?? json.file.name;

            figureContainer.appendChild(media);
            figureContainer.removeChild(figureContainer.querySelector('.loader-container'));
            figureContainer.classList.remove('media-uploading');
            figureContainer.removeAttribute('id');

            then(json);
          })
          .catch(error => Util.logError(error));
      } catch (error) {
        Util.logError(error);
      }
    }
  }

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
      Util.logError(new Error(file.name + ' is not an image'));
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

          Util.logError(error);
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

          Util.logError(error);
          catchError();
        });
    });

    reader.readAsDataURL(file);
  }
}

export const Uploader = new UploaderClass();
