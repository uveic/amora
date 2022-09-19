import {xhr} from './xhr.js';
import {global} from './localisation-001.js';

class UploaderClass {
  static logError(userFeedbackDiv, error) {
    if (userFeedbackDiv) {
      userFeedbackDiv.textContent = error.message;
      userFeedbackDiv.classList.remove('feedback-success');
      userFeedbackDiv.classList.add('feedback-error');
      userFeedbackDiv.classList.remove('null');
      setTimeout(() => {userFeedbackDiv.classList.add('null')}, 15000);
    }
  }

  static buildImageLoadingElement() {
    let imgLoading = new Image();
    imgLoading.className = 'justify-center';
    imgLoading.alt = global.get('globalLoading');
    imgLoading.src = '/img/loading.gif';

    return imgLoading;
  }

  uploadImage(
    file,
    imageContainer,
    imageClassName,
    userFeedbackDiv,
    then = () => {},
    catchError = () => {},
    apiUploadEndpoint = '/api/file',
    formData = new FormData(),
  ) {
    if (!/\.(jpe?g|png|gif|webp)$/i.test(file.name)) {
      UploaderClass.logError(userFeedbackDiv, new Error(file.name + ' is not an image'));
      return;
    }

    formData.append('files[]', file);

    const reader = new FileReader();
    reader.addEventListener('load', function () {
      let image = new Image();
      image.className = 'opacity ' + imageClassName;
      image.src = String(reader.result);

      const imgLoading = UploaderClass.buildImageLoadingElement();

      imageContainer.appendChild(imgLoading);
      imageContainer.firstChild
        ? imageContainer.insertBefore(image, imageContainer.firstChild)
        : imageContainer.appendChild(image);

      xhr.postImage(apiUploadEndpoint, formData, userFeedbackDiv)
        .then(response => {
          if (!response.success || !response.file) {
            throw new Error(response.errorMessage ?? global.get('genericError'));
          }

          image.classList.remove('opacity');
          image.src = response.file.url;
          image.dataset.imageId = response.file.id;
          image.alt = response.file.caption ?? response.file.name;
          imageContainer.removeChild(imgLoading);

          then(response);
        })
        .catch((error) => {
          imageContainer.removeChild(imgLoading);
          imageContainer.removeChild(image);

          UploaderClass.logError(userFeedbackDiv, error);
          catchError();
        });
    })

    reader.readAsDataURL(file);
  };

  uploadFile(
    file,
    container,
    fileClassName,
    userFeedbackDiv,
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

      xhr.postImage(apiUploadEndpoint, formData, userFeedbackDiv)
        .then(data => {
          if (!data.success) {
            throw new Error(data.errorMessage ?? global.get('genericError'));
          }

          container.removeChild(fileNameSpan);
          container.removeChild(imgLoading);

          then(data);
        })
        .catch((error) => {
          container.removeChild(fileNameSpan);
          container.removeChild(imgLoading);

          UploaderClass.logError(userFeedbackDiv, error);
          catchError();
        });
    });

    reader.readAsDataURL(file);
  }
}

export const Uploader = new UploaderClass();
