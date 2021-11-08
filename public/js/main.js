import {xhr} from './module/xhr.js';

document.querySelectorAll('.blog-posts-load-more').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const loadingEl = document.querySelector('.loading-blog-posts');
    loadingEl.classList.remove('null');

    xhr.get('/papi/ping')
      .then(response => {
        console.log(response);

        loadingEl.classList.add('null');
      });
  });
});
