const changeMedia = () => {
  document.querySelectorAll('section.js-content-slider-fade').forEach(s => {
    const activeImage = s.querySelector('img.media-opacity-active') ?? s.querySelector('img.media-item');
    const nextImage = s.querySelector('img.media-opacity-active + img') ?? s.querySelector('.media-wrapper img');

    activeImage.classList.remove('media-opacity-active');
    activeImage.classList.add('media-opacity-hidden');

    nextImage.classList.add('media-opacity-active');
    nextImage.classList.remove('media-opacity-hidden');
  });
};

setInterval(changeMedia, 8000);

document.querySelectorAll('a.js-media-read-more').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    const sectionId = a.dataset.sectionId;
    document.querySelector('#media-text-panel-' + sectionId).classList.remove('null');
  });
});

document.querySelectorAll('a.js-media-view').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    a.parentElement.parentElement.parentElement.parentElement.querySelector('.js-navigation-right').click();
  });
});

document.querySelectorAll('a.media-panel-close').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    a.parentElement.classList.add('null');
  });
});

const getNextImage = (mediaContainer, goRight = true) => {
  if (goRight) {
    return mediaContainer.querySelector('img.media-active + img.media-item')
      ?? mediaContainer.querySelector('img.media-item');
  }

  const images = [];
  let mediaActiveKey = null;
  let key = 0;
  mediaContainer.querySelectorAll('img.media-item').forEach(mi => {
    if (mi.classList.contains('media-active')) {
      mediaActiveKey = key;
    }

    images[key] = mi;
    key++;
  })

  if (!images) {
    return null;
  }

  if (mediaActiveKey === null || mediaActiveKey === images.length) {
    return mediaContainer.querySelector('img.media-item');
  }

  if (mediaActiveKey === 0) {
    return mediaContainer.querySelector('img.media-item:last-of-type');
  }

  return images[mediaActiveKey - 1];
};

document.addEventListener("DOMContentLoaded", () => {
  let options = {
    root: null,
    rootMargin: "0px",
    threshold: 0.25
  };

  function handleIntersect(entries, observer) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('js-content-child-active');
        const textPanel = entry.target.querySelector('.media-text-panel');
        if (textPanel) {
          textPanel.classList.add('null');
        }

        const mediaContainer = entry.target.querySelector('.media-wrapper');
        const nextImageToPreload = getNextImage(mediaContainer);
        const imgTemp = new Image();
        imgTemp.src = nextImageToPreload.src;

        const contentText = entry.target.querySelector('.content-text');
        if (contentText) {
          contentText.classList.remove('null');
        }
      } else {
        entry.target.classList.remove('js-content-child-active');
      }
    });
  }

  const observer = new IntersectionObserver(handleIntersect, options);
  document.querySelectorAll('.content-child').forEach(cc => observer.observe(cc));
})

document.querySelectorAll('.js-navigation-left, .js-navigation-right').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();

    const mediaContainer = a.parentElement.parentElement.parentElement.querySelector('.media-wrapper');
    const activeImage = mediaContainer.querySelector('img.media-active')
      ?? mediaContainer.querySelector('img.media-item');
    const goRight = a.classList.contains('js-navigation-right');
    const nextImage = getNextImage(mediaContainer, goRight);

    if (!activeImage || !nextImage) {
      return;
    }

    activeImage.classList.remove('media-active');
    activeImage.classList.add('media-hidden');

    nextImage.classList.add('media-active');
    nextImage.classList.remove('media-hidden');

    const contentText = mediaContainer.parentElement.querySelector('.media-content-wrapper .content-text');
    if (contentText) {
      contentText.classList.add('null');
    }

    const mediaTextPanel = mediaContainer.parentElement.querySelector('.media-text-panel');
    if (mediaTextPanel) {
      mediaTextPanel.classList.add('null');
    }

    const contentMediaSequence = mediaContainer.parentElement.querySelector('.media-sequence');
    if (contentMediaSequence) {
      contentMediaSequence.textContent = nextImage.dataset.sequence;
    }

    const nextImageToPreload = getNextImage(mediaContainer, goRight);
    const imgTemp = new Image();
    imgTemp.src = nextImageToPreload.src;
  });
});

document.querySelectorAll('.menu-button').forEach(b => {
  b.addEventListener('click', e => {
    e.preventDefault();
    document.querySelector('.album-new-york-sections-modal-js').classList.add('modal-wrapper-open');
  });
});

document.querySelectorAll('.modal-close-button').forEach(b => {
  b.addEventListener('click', e => {
    document.querySelector('.album-new-york-sections-modal-js').classList.remove('modal-wrapper-open');
  });
});

document.querySelectorAll('.js-section-item').forEach(b => {
  b.addEventListener('click', e => {
    e.preventDefault();

    const sectionId = b.dataset.sectionId;
    document.querySelector('.album-new-york-sections-modal-js').classList.remove('modal-wrapper-open');
    document.querySelector('.content-child[data-section-id="' + sectionId + '"]')
      .scrollIntoView({behavior: 'smooth', block: 'start' });
  });
});

document.addEventListener('keydown', e => {
  if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
    return;
  }

  const modal = document.querySelector('.album-new-york-sections-modal-js');
  if (modal && modal.classList.contains('modal-wrapper-open')) {
    return;
  }

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    const nextActiveContent = document.querySelector('.js-content-child-active + .content-child');
    if (nextActiveContent) {
      nextActiveContent.scrollIntoView({behavior: 'smooth', block: 'start' });
    }
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    let previous = null;
    document.querySelectorAll('.content-main .content-child').forEach(cc => {
      if (cc.classList.contains('js-content-child-active') && previous) {
        previous.scrollIntoView({behavior: 'smooth', block: 'start' });
      }
      previous = cc;
    });
  } else if (e.key === 'ArrowRight') {
    const activeContent = document.querySelector('.js-content-child-active');
    if (activeContent) {
      const rightActionEl = activeContent.querySelector('.js-navigation-right');
      if (rightActionEl) {
        rightActionEl.click();
      }
    }
  } else if (e.key === 'ArrowLeft') {
    const activeContent = document.querySelector('.js-content-child-active');
    if (activeContent) {
      const rightActionEl = activeContent.querySelector('.js-navigation-left');
      if (rightActionEl) {
        rightActionEl.click();
      }
    }
  }
});