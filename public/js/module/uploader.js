import {xhr} from './xhr.js';
import {global} from './localisation.js';

const logError = (userFeedbackDiv, error) => {
  if (userFeedbackDiv) {
    userFeedbackDiv.textContent = error.message;
    userFeedbackDiv.classList.remove('feedback-success');
    userFeedbackDiv.classList.add('feedback-error');
    userFeedbackDiv.classList.remove('null');
    setTimeout(() => {userFeedbackDiv.classList.add('null')}, 15000);
  }
};

function uploadImage(
  file,
  imageContainer,
  imageClassName,
  userFeedbackDiv,
  then = () => {},
  catchError = () => {},
  apiUploadEndpoint = '/api/image',
) {
  if (!/\.(jpe?g|png|gif|webp)$/i.test(file.name)) {
    logError(userFeedbackDiv, new Error(file.name + ' is not an image'));
    return;
  }

  let formData = new FormData();
  formData.append('files[]', file);

  const reader = new FileReader();
  reader.addEventListener('load', function () {
    let image = new Image();
    image.className = 'opacity ' + imageClassName;
    image.src = String(reader.result);

    let imgLoading = new Image();
    imgLoading.className = 'justify-center';
    imgLoading.alt = global.get('globalLoading');
    imgLoading.src = '/img/loading.gif';

    imageContainer.appendChild(image);
    imageContainer.appendChild(imgLoading);

    xhr.postImage(apiUploadEndpoint, formData, userFeedbackDiv)
      .then(data => {
        if (!data.success || !data.images || data.images.length <= 0) {
          throw new Error(data.errorMessage ?? global.get('genericError'));
        }

        const uploadedImageData = data.images[0];
        image.classList.remove('opacity');
        image.src = uploadedImageData.url;
        image.dataset.imageId = uploadedImageData.id;
        image.alt = uploadedImageData.caption ?? '';
        imageContainer.removeChild(imgLoading);

        then(uploadedImageData);
      })
      .catch((error) => {
        logError(userFeedbackDiv, error);
        catchError();
      });
  });

  reader.readAsDataURL(file);
}

export {uploadImage};
