import {xhr} from './module/xhr.js';
import {global} from './module/localisation.js';

document.querySelectorAll('.blog-posts-load-more').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const loadingEl = document.querySelector('.loading-blog-posts');
    loadingEl.classList.remove('null');

    const offset = Number.parseInt(el.dataset.offset);
    const itemsPerPage = Number.parseInt(el.dataset.itemsPerPage);

    xhr.get('/papi/blog/post?offset=' + offset + '&itemsPerPage=' + itemsPerPage)
      .then(response => {
        const postsParent = document.querySelector('div.blog-items');

        response.blogPosts.forEach(bp => {
          const postLink = document.createElement('a');
          postLink.href = bp.postUri;
          postLink.className = 'link-title';
          postLink.textContent = bp.postTitle;

          const spanBlogInfo = document.createElement('span');
          spanBlogInfo.className = 'blog-info';
          const publishedOn = new Date(bp.publishedOn);
          spanBlogInfo.textContent = global.formatDate(publishedOn, false, true, false);

          const postItem = document.createElement('div');
          postItem.className = 'blog-item';

          postItem.appendChild(postLink);
          postItem.appendChild(spanBlogInfo);
          postsParent.appendChild(postItem);
        });

        el.dataset.offset = response.pagination.offset;
        el.dataset.itemsPerPage = response.pagination.itemsPerPage;

        if (response.blogPosts.length < response.pagination.itemsPerPage) {
          document.querySelector('.blog-posts-load-more').classList.add('null');
        }

        loadingEl.classList.add('null');
      });
  });
});
