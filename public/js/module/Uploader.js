import {Global} from './localisation.js?v=000';
import {Util} from "./Util.js?v=000";

class UploaderClass {
  static buildProgressElement() {
    let loaderContainer = document.createElement('div');
    loaderContainer.className = 'loader-container';

    let progressEl = document.createElement('progress');
    progressEl.max = 100;
    progressEl.value = 0;
    loaderContainer.appendChild(progressEl);

    return loaderContainer;
  }

  uploadImageBefore(media, mediaContainer) {
    const loaderEl = Util.buildImageLoadingElement();
    const figureContainer = document.createElement('figure');
    figureContainer.className = 'image-container';
    figureContainer.id = 'item' + Util.cleanString(media.name);

    figureContainer.appendChild(loaderEl);

    mediaContainer.appendChild(figureContainer);
    if (mediaContainer.firstChild) {
      mediaContainer.insertBefore(figureContainer, mediaContainer.firstChild);
    } else {
      mediaContainer.appendChild(figureContainer);
    }
  }

  uploadFileBefore(media, mediaContainer) {
    const loaderEl = Util.buildImageLoadingElement();
    const fileContainer = document.createElement('div');
    fileContainer.className = 'file-container';
    fileContainer.id = 'item' + Util.cleanString(media.name);
    fileContainer.appendChild(loaderEl);

    mediaContainer.appendChild(fileContainer);
    if (mediaContainer.firstChild) {
      mediaContainer.insertBefore(fileContainer, mediaContainer.firstChild);
    } else {
      mediaContainer.appendChild(fileContainer);
    }
  }

  uploadImageThen(json, imageClassName) {
    if (!json.success || !json.file) {
      throw new Error(json.errorMessage ?? Global.get('genericError'));
    }

    const figureContainer = document.querySelector('#item' + Util.cleanString(json.file.sourceName));

    const media = new Image();
    media.className = imageClassName;
    media.src = json.file.pathSmall;
    media.sizes = json.file.sizes;
    media.srcset = json.file.srcset;
    media.dataset.mediaId = json.file.id;
    media.dataset.pathLarge = json.file.pathLarge;
    media.alt = json.file.caption ?? json.file.name;

    figureContainer.appendChild(media);
    figureContainer.removeChild(figureContainer.querySelector('.loader-container'));
    figureContainer.removeAttribute('id');
  }

  uploadFileThen(json) {
    if (!json.success || !json.file) {
      throw new Error(json.errorMessage ?? Global.get('genericError'));
    }

    const fileContainer = document.querySelector('#item' + Util.cleanString(json.file.sourceName));
    fileContainer.removeChild(fileContainer.querySelector('.loader-container'));
    fileContainer.innerHTML = json.file.asHtml;
  }

  async uploadFile (
    media,
    apiUploadEndpoint = '/api/file',
    formData = new FormData(),
  ) {
    return new Promise((resolve, reject) => {
      const itemContainer = document.querySelector('#item' + Util.cleanString(media.name));
      const progressBarContainer = UploaderClass.buildProgressElement();
      const progressEl = progressBarContainer.querySelector('progress');
      if (itemContainer) {
        itemContainer.appendChild(progressBarContainer);
        itemContainer.removeChild(itemContainer.querySelector('.loader-container'));
      }

      formData.delete('files[]');
      formData.append('files[]', media);

      const xhr = new XMLHttpRequest();
      xhr.addEventListener('load', () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
          resolve(JSON.parse(xhr.response));
        } else {
          reject(new Error(xhr.statusText));
        }
      });

      xhr.upload.addEventListener('progress', (e) => {
        const progressValue = Math.round((e.loaded / e.total) * 100);
        progressEl.value = progressValue;

        if (progressValue >= 95) {
          if (itemContainer) {
            itemContainer.removeChild(progressBarContainer);
            itemContainer.appendChild(Util.buildImageLoadingElement());
          }
        }
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
    imageClassName = 'media-item',
    apiUploadEndpoint = '/api/file',
    formData = new FormData(),
  ) {
    for (const media of Array.from(files)) {
      const isImage = /\.(jpe?g|png|gif|webp)$/i.test(media.name);

      if (isImage) {
        this.uploadImageBefore(media, mediaContainer);
      } else {
        this.uploadFileBefore(media, mediaContainer);
      }
    }

    for (const media of Array.from(files)) {
      try {
        const isImage = /\.(jpe?g|png|gif|webp)$/i.test(media.name);

        await this.uploadFile(media, apiUploadEndpoint, formData)
          .then(json => {
            if (isImage) {
              this.uploadImageThen(json, imageClassName);
            } else {
              this.uploadFileThen(json, imageClassName);
            }

            then(json);
          })
          .catch(error => {
            const figureContainer = document.querySelector('#item' + Util.cleanString(media.name));
            if (figureContainer) {
              mediaContainer.removeChild(figureContainer);
            }

            Util.notifyError(error);
            catchError(error);
          });
      } catch (error) {
        Util.notifyError(error);
        catchError(error);
      }
    }
  }

  trixEditorUploadFile (
    attachment,
    apiUploadEndpoint = '/api/file',
    formData = new FormData(),
  ) {
    formData.delete('files[]');
    formData.append('files[]', attachment.file);

    let xhr = new XMLHttpRequest();
    xhr.addEventListener('load', () => {
      if (xhr.readyState === 4 && xhr.status === 200) {
        const json = JSON.parse(xhr.response);
        attachment.setAttributes({
          url: json.file.pathLarge,
        });
      }
    });

    xhr.upload.addEventListener('progress', (e) => {
      const progressValue = Math.round((e.loaded / e.total) * 100);
      attachment.setUploadProgress(progressValue);
    });

    xhr.open('POST', apiUploadEndpoint);
    xhr.send(formData);
  }
}

export const Uploader = new UploaderClass();
