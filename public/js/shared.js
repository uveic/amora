import {Util} from "./module/Util.js?v=000";

function handleDropdownOptionClick(event) {
  event.preventDefault();
  const elementOption = event.currentTarget;
  const dropDownIdentifier = elementOption.dataset.dropdownIdentifier;
  const elementLabel = document.querySelector('#' + dropDownIdentifier + '-dd-label');
  const elementCheckbox = document.querySelector('#' + dropDownIdentifier + '-dd-checkbox');
  const optionClassName = dropDownIdentifier + '-dd-option';

  elementLabel.classList.forEach(cl => {
    if (cl.startsWith('status-')) {
      elementLabel.classList.remove(cl);
    }
  });

  const newClassName = Array.from(elementOption.classList).filter(cl => cl.startsWith('status-') === true)[0] ?? null;
  if (newClassName) {
    elementLabel.classList.add(newClassName);
  }
  elementLabel.querySelector('span').innerHTML = elementOption.innerHTML;
  elementCheckbox.checked = false;

  document.querySelectorAll('.' + optionClassName).forEach(o => {
    o.dataset.checked = o.dataset.value === elementOption.dataset.value ? '1' : '0';
  });
}

window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.dropdown-menu-option').forEach(op => {
    op.addEventListener('click', handleDropdownOptionClick);
  });

  document.querySelectorAll('.filter-close').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelector('.filter-container').classList.add('null');
    });
  });

  document.querySelectorAll('.filter-open').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelector('.filter-container').classList.remove('null');
    });
  });

  document.querySelectorAll('.form-filter').forEach(ff => {
    ff.addEventListener('submit', e => {
      e.preventDefault();

      const query = new URLSearchParams();

      ff.querySelectorAll('select').forEach(s => {
        if (s.dataset.paramName && s.value) {
          query.append(s.dataset.paramName, s.value);
        }
      });

      if (!query.entries()) {
        document.querySelector('.filter-container').classList.remove('null');
        return;
      }

      window.location.href = window.location.origin
        + window.location.pathname
        + (query.entries() ? '?' + query.toString() : '');
    });
  });

  document.querySelectorAll('.modal-open-js').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      document.body.style.overflow = 'hidden';
    });
  });

  document.querySelectorAll('.modal-close-button, .modal-close-link').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      document.body.style.overflow = 'auto';
      el.closest('.modal-wrapper').classList.add('null');
    });
  });

  document.querySelectorAll('.modal-media-close').forEach(mm => {
    mm.addEventListener('click', (e) => {
      e.preventDefault();
      const modal = document.querySelector('.modal-media');
      if (!modal) {
        return;
      }

      modal.classList.add('null');

      const youtubeIframeContainer = modal.querySelector('.youtube-video iframe');
      if (youtubeIframeContainer) {
        youtubeIframeContainer.parentElement.removeChild(youtubeIframeContainer);
      }
    });
  });

  document.addEventListener('keydown', e => {
    if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
      return;
    }

    const modalMediaCloseEl = document.querySelector('.modal-media .modal-media-close');
    if (!modalMediaCloseEl) {
      return;
    }

    if (e.key === 'Escape') {
      e.preventDefault();
      modalMediaCloseEl.click();
    }
  });

  document.querySelectorAll('.copy-link').forEach(a => {
    a.addEventListener('click', e => Util.handleCopyLink(e, a.href));
  });

  document.querySelectorAll('.back-js').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      window.history.back();
    });
  });
});

document.querySelectorAll('.image-popup-js').forEach(image => {
  image.addEventListener('click', (e) => {
    e.preventDefault();

    const modal = document.querySelector('.modal-display-image');
    if (!modal) {
      return;
    }

    const modalInner = modal.querySelector('.modal-inner');
    const popupImage = modal.querySelector('img.modal-display-item');

    if (popupImage) {
      popupImage.parentElement.removeChild(popupImage);
    }

    let modalImage = new Image();
    modalImage.className = 'modal-display-item';
    modalImage.src = image.dataset.pathMedium ?? image.src;
    modalImage.alt = image.alt;
    modalImage.title = image.title;

    modalInner.appendChild(modalImage);

    modal.classList.remove('null');
  });
});

export {handleDropdownOptionClick};