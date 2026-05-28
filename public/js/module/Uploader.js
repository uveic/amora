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

  uploadMediaBefore(media, mediaContainer) {
    const isImage = this.isImage(media.name);

    const loaderEl = Util.buildImageLoadingElement();
    const container = document.createElement(isImage ? 'figure' : 'div');
    container.className = isImage ? 'image-container' : 'file-container';
    container.id = 'item' + Util.cleanString(media.name);

    container.appendChild(loaderEl);

    mediaContainer.appendChild(container);
    if (mediaContainer.firstChild) {
      mediaContainer.insertBefore(container, mediaContainer.firstChild);
    } else {
      mediaContainer.appendChild(container);
    }
  }

  uploadMediaThen(json, imageClassName, isImage) {
    if (!json.success || !json.file) {
      throw new Error(json.errorMessage ?? Global.get('genericError'));
    }

    const container = document.querySelector('#item' + Util.cleanString(json.file.sourceName));
    if (!container) {
      return;
    }

    if (isImage) {
      const media = new Image();
      media.dataset.mediaId = json.file.id;
      media.className = imageClassName;
      media.alt = json.file.caption ?? json.file.name;

      if (json.file.sizes) {
        media.sizes = json.file.sizes;
      }
      if (json.file.srcset) {
        media.srcset = json.file.srcset;
        media.src = json.file.pathSmall;
      } else {
        media.src = json.file.pathMedium;
      }
      if (json.file.pathLarge) {
        media.dataset.pathLarge = json.file.pathLarge;
      }

      container.appendChild(media);
    } else {
      container.innerHTML = json.file.asHtml;
    }

    container.querySelectorAll('.loader-container').forEach(lc => lc.remove());
    container.removeAttribute('id');
  }

  async uploadMedia (
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
      }

      formData.delete('files[]');
      formData.append('files[]', media);

      const xhr = new XMLHttpRequest();
      xhr.addEventListener('load', () => {
        if (xhr.readyState === 4 && xhr.status === 200) {
          try {
            resolve(JSON.parse(xhr.response));
          } catch (e) {
            reject(e);
          }
        } else {
          reject(new Error(xhr.statusText));
        }
      });

      xhr.upload.addEventListener('progress', (e) => {
        const progressValue = Math.round((e.loaded / e.total) * 100);
        progressEl.value = progressValue;

        if (progressValue >= 95) {
          if (itemContainer) {
            progressBarContainer.remove();
            itemContainer.appendChild(Util.buildImageLoadingElement());
          }
        }
      });

      xhr.open('POST', apiUploadEndpoint);
      xhr.send(formData);
    });
  }

  isImage(mediaName) {
    return /\.(jpe?g|png|gif|webp|svg)$/i.test(mediaName);
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
      this.uploadMediaBefore(media, mediaContainer);
    }

    for (const media of Array.from(files)) {
      try {
        await this.uploadMedia(media, apiUploadEndpoint, formData)
          .then(json => {
            this.uploadMediaThen(json, imageClassName, this.isImage(media.name));
            then(json);
          })
          .catch(error => {
            document.querySelectorAll('#item' + Util.cleanString(media.name)).forEach(i => i.remove());
            Util.notifyError(error);
            catchError(error);
          });
      } catch (error) {
        Util.notifyError(error);
        catchError(error);
      }
    }
  }
}

export const Uploader = new UploaderClass();
