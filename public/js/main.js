import {Request} from './module/Request.js?v=000';
import {Global} from './module/localisation.js?v=000';

window.addEventListener('DOMContentLoaded', e => {
  document.querySelectorAll('.blog-posts-load-more').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();

      const loadingEl = document.querySelector('.loading-blog-posts');
      loadingEl.classList.remove('null');

      const offset = Number.parseInt(el.dataset.offset);
      const itemsPerPage = Number.parseInt(el.dataset.itemsPerPage);

      Request.get('/papi/blog/post?offset=' + offset + '&itemsPerPage=' + itemsPerPage)
        .then(response => {
          const postsParent = document.querySelector('div.blog-items');
          const years = [];
          postsParent.querySelectorAll('.blog-item-year').forEach(y => {
            years.push(Number.parseInt(y.innerText))
          });
          let year = years.pop();

          response.blogPosts.forEach(bp => {
            const publishedOn = new Date(bp.publishedOn);

            if (publishedOn.getFullYear() !== year) {
              const yearEl = document.createElement('h2');
              yearEl.classList.add('blog-item-year');
              year = publishedOn.getFullYear();
              yearEl.innerText = year;
              postsParent.appendChild(yearEl);
            }

            const postLink = document.createElement('a');
            postLink.href = bp.path;
            postLink.className = 'link-title';
            postLink.textContent = bp.title;

            const spanBlogInfo = document.createElement('span');
            spanBlogInfo.className = 'blog-info';
            spanBlogInfo.textContent = Global.formatDate(publishedOn, false, true, false);

            const postItem = document.createElement('div');
            postItem.className = 'blog-item';

            const postItemTitle = document.createElement('span');
            const icon = document.createElement('span');
            icon.innerHTML = bp.icon;
            postItemTitle.appendChild(icon);
            postItemTitle.appendChild(postLink);

            postItem.appendChild(postItemTitle);
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

  document.querySelectorAll('.home-link-scroll').forEach(a => {
    a.addEventListener('click', e => {
      if (!a.hash || !a.hash.length) {
        return;
      }

      const el = document.querySelector(a.hash);
      if (!el) {
        return;
      }

      e.preventDefault();
      el.scrollIntoView({behavior: 'smooth', block: 'start' });
      history.replaceState(undefined, undefined, a.hash);

      const inputCheckbox = document.querySelector('input.mobile-nav');
      if (inputCheckbox) {
        inputCheckbox.checked = false;
      }
    });
  });
});
