import {global} from "./localisation.js";

async function uploadImage(file, containerToAppendImages, userFeedbackDiv, payloadData = {}) {
  console.log(-1);
  const apiUploadEndpoint = '/api/image';

  if (!/\.(jpe?g|png|gif|webp)$/i.test(file.name)) {
    return Promise.reject(file.name + " is not an image");
  }

  let formData = new FormData();

  formData.append('files[]', file);
  for (let i in payloadData) {
    formData.append(i, payloadData[i]);
  }

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
    containerToAppendImages.appendChild(articleImageDiv);

    console.log(0);

    return fetch(
      apiUploadEndpoint,
      {
        method: 'POST',
        body: formData
      }
    ).then(response => {
      console.log(1);

      return response.json();
    }).then(json => {
      console.log(2);
      if (!json.success || !json.images || json.images.length <= 0) {
        throw new Error(
          json.errorMessage ?? global.get('genericError') + ': ' + image.title
        );
      }

      return Promise.resolve({
        apiJsonResponse: json,
        image: image,
        imageDiv: articleImageDiv,
        imageLoading: imgLoading
      });
    }).catch((error) => {
      articleImageDiv.classList.add('null');
      userFeedbackDiv.textContent = error.message;
      userFeedbackDiv.classList.remove('feedback-success');
      userFeedbackDiv.classList.add('feedback-error');
      userFeedbackDiv.classList.remove('null');
      setTimeout(() => {
        userFeedbackDiv.classList.add('null')
      }, 5000);
      console.log('Here');
      console.log(error);

      return Promise.reject(error.message);
    });
  });

  reader.readAsDataURL(file);
}

export {uploadImage};
