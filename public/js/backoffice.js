import {cleanString, getUpdatedAtTime} from './module/util.js';
import {xhr} from './module/xhr.js';
import {feedbackDiv} from './authorised.js';
import {global} from "./module/localisation.js";
import {classes as pexegoClasses} from "./module/pexego.js";
import {uploadImage} from "./module/imageUploader.js";

let globalTags = [];

document.querySelectorAll('.article-save-button').forEach(el => {
  el.addEventListener('click', e => {
    e.preventDefault();

    const getSectionTypeIdFromClassList = (classList) => {
      if (classList.contains(pexegoClasses.sectionParagraph)) {
        return 1;
      }

      if (classList.contains(pexegoClasses.sectionImage)) {
        return 2;
      }

      if (classList.contains(pexegoClasses.sectionVideo)) {
        return 3;
      }
    };

    const afterApiCall = function(articleId, articlePublicUri, articleBackofficeUri) {
      history.pushState("", document.title, articleBackofficeUri);
      document.querySelector('input[name="articleId"]').value = articleId;

      document.querySelectorAll('.control-bar-creation').forEach(a => a.classList.remove('hidden'));
      document.querySelectorAll('span.article-updated-at').forEach(s => {
        s.textContent = getUpdatedAtTime();
      });

      document.querySelectorAll('#side-options').forEach(i => i.classList.add('null'));
      document.querySelectorAll('.article-save-button').forEach(b => {
        b.value = global.get('globalUpdate');
      });
      document.querySelectorAll('.pexego-preview').forEach(b => {
        b.href = articlePublicUri;
        b.classList.remove('null');
      });
      document.querySelectorAll('input[name="articleUri"]').forEach(i => {
        i.value = articlePublicUri.trim().replace(/^\//,"");
      });
    };

    const getTitleContent = () => {
      const titleEl = document.querySelector('input[name="articleTitle"]');
      if (titleEl && titleEl.value.trim().length) {
        return titleEl.value.trim();
      }

      return null;
    };

    const getTags = () => {
      let tags = [];
      document.querySelectorAll('#tags-selected > .result-selected')
        .forEach(t => {
          tags.push({
            id: t.dataset.tagId ? Number.parseInt(t.dataset.tagId) : null,
            name: t.dataset.tagName
          });
        });

      return tags;
    };

    const getContentHtmlAndSections = () => {
      let articleContentHtml = '';
      let sections = [];
      let order = 1;
      let mainImageId = null;

      document.querySelectorAll('.pexego-section').forEach(originalSection => {
        let section = originalSection.cloneNode(true);
        let editorId = section.dataset.sectionId ?? section.dataset.editorId;

        let sectionElement = section.classList.contains(pexegoClasses.sectionParagraph)
          ? document.querySelector('#' + pexegoClasses.sectionParagraph + '-' + editorId + '-html')
          : section;

        let sectionContentHtml = sectionElement.innerHTML.trim();

        for (let i = 0; i < sectionElement.children.length; i++) {
          let c = sectionElement.children[i];

          if (c.classList.contains(pexegoClasses.contentImageCaption)) {
            c.classList.add('article-section-image-caption');
            if (c.textContent.trim() === c.dataset.placeholder) {
              c.textContent = '';
            }
          }

          if (c.classList.contains(pexegoClasses.contentParagraph)
            && c.textContent.trim() === c.dataset.placeholder
          ) {
            c.textContent = '';
          }

          c.classList.remove(
            pexegoClasses.contentParagraph,
            pexegoClasses.contentImage,
            pexegoClasses.contentImageCaption
          );
          if (!c.classList.length) {
            c.removeAttribute('class');
          }
          c.removeAttribute('id');
          c.removeAttribute('contenteditable');
          delete c.dataset.placeholder;
          delete c.dataset.imageId;

          if (c.nodeName !== 'IMG' && !c.innerHTML.trim().length) {
            sectionElement.removeChild(c);
          }
        }

        let elementContent = sectionElement.innerHTML.trim();
        if (elementContent.length) {
          articleContentHtml += elementContent;
        }

        let currentSection = {
          id: section.dataset.sectionId ? Number.parseInt(section.dataset.sectionId) : null,
          sectionTypeId: getSectionTypeIdFromClassList(section.classList),
          contentHtml: sectionContentHtml,
          order: order++
        };

        if (section.classList.contains(pexegoClasses.sectionImage)) {
          const imageCaption = originalSection.querySelector('.' + pexegoClasses.contentImageCaption);
          currentSection.imageCaption = imageCaption && imageCaption.length && imageCaption.dataset.placeholder !== imageCaption.textContent.trim()
            ? imageCaption.textContent.trim()
            : null;
          const image = originalSection.querySelector('.' + pexegoClasses.contentImage);
          currentSection.imageId = image ? Number.parseInt(image.dataset.imageId) : null;
          if (!mainImageId && currentSection.imageId) {
            mainImageId = currentSection.imageId;
          }
        }

        sections.push(currentSection);
      });

      return {
        sections: sections,
        contentHtml: articleContentHtml.trim().length ? articleContentHtml.trim() : null,
        mainImageId: mainImageId,
      };
    };

    const getPublishOnDateIsoString = () => {
      const publishOnDateEl = document.querySelector('input[name="publishOnDate"]');
      const publishOnTimeEl = document.querySelector('input[name="publishOnTime"]');

      return publishOnDateEl.value && publishOnTimeEl.value
        ? new Date(publishOnDateEl.value + 'T' + publishOnTimeEl.value + ':00').toISOString()
        : null;
    };

    const getArticleTypeId = () => {
      const articleTypeIdEl = document.querySelector('input[name="articleTypeId"]');
      return articleTypeIdEl && articleTypeIdEl.value.length
        ? Number.parseInt(articleTypeIdEl.value)
        : null;
    }

    const getUri = () => {
      const uriEl = document.querySelector('input[name="articleUri"]');
      return uriEl && uriEl.value.trim().length ? uriEl.value.trim() : null;
    }

    const getStatusId = () => {
      const status = document.querySelector('.dropdown-menu-option[data-checked="1"]');
      return Number.parseInt(status.dataset.articleStatusId);
    };

    const articleTitle = getTitleContent();
    const content = getContentHtmlAndSections();

    if (!articleTitle && !content.contentHtml) {
      feedbackDiv.textContent = global.get('feedbackSaving');
      feedbackDiv.classList.remove('feedback-error');
      feedbackDiv.classList.add('feedback-success');
      feedbackDiv.classList.remove('null');
      setTimeout(() => {feedbackDiv.classList.add('null')}, 5000);
      document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
      return;
    }

    const articleUri = getUri();
    document.querySelectorAll('.article-saving').forEach(ar => ar.classList.remove('null'));

    const payload = JSON.stringify({
      languageIsoCode: document.documentElement.lang ?? null,
      title: articleTitle,
      uri: articleUri,
      contentHtml: content.contentHtml,
      typeId: getArticleTypeId(),
      statusId: getStatusId(),
      mainImageId: content.mainImageId,
      sections: content.sections,
      tags: getTags(),
      publishOn: getPublishOnDateIsoString(),
    });

    const articleIdEl = document.querySelector('input[name="articleId"]');
    const url = '/back/article';
    if (articleIdEl && articleIdEl.value) {
      xhr.put(url + '/' + articleIdEl.value, payload, feedbackDiv, global.get('globalUpdated'))
        .then((res) => afterApiCall(res.articleId, res.articlePublicUri, res.articleBackofficeUri))
        .finally(() => {
          document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
        });
    } else {
      xhr.post(url, payload, feedbackDiv, global.get('globalSaved'))
        .then((res) => afterApiCall(res.articleId, res.articlePublicUri, res.articleBackofficeUri))
        .finally(() => {
          document.querySelectorAll('.article-saving').forEach(ar => ar.classList.add('null'));
        });
    }
  });
});

const addMouseListenerToImageInImagesSection = function (imageEl) {
  let currentImgId = imageEl.dataset.imageId;

  imageEl.onmouseover = function () {
    document.querySelector('#image-options-' + currentImgId).classList.remove('null');
  }

  imageEl.onmouseout = function () {
    document.querySelector('#image-options-' + currentImgId).classList.add('null');
  }
}

document.querySelectorAll('.image-item').forEach(im => addMouseListenerToImageInImagesSection(im));

const deleteImage = async function (e, aEl) {
  e.preventDefault();

  const delRes = confirm(global.get('feedbackDeleteImageConfirmation'));
  if (!delRes) {
    return;
  }

  const imageId = Number.parseInt(aEl.parentNode.parentNode.dataset.imageId);

  xhr.delete('/api/image/' + imageId, feedbackDiv, global.get('feedbackImageDeleted'))
    .then(() => {
      const iDiv = document.querySelector(".image-item[data-image-id='" + imageId + "']");
      iDiv.classList.add('null');
    });
}
document.querySelectorAll('.image-delete').forEach(function (aEl) {
  aEl.addEventListener('click', e => {
    deleteImage(e, aEl).then();
  });
});

document.querySelectorAll('input#images').forEach(im => {
  im.addEventListener('change', e => {
    e.preventDefault();

    const imagesContainer = document.querySelector('#images-list');
    let newImgDiv = document.createElement('div');
    newImgDiv.className = 'image-item';

    imagesContainer.appendChild(newImgDiv);

    for (let i = 0; i < im.files.length; i++) {
      let file = im.files[i]

      uploadImage(
        file,
        newImgDiv,
        '',
        feedbackDiv,
        (response) => {
          if (response && response.id) {
            newImgDiv.dataset.imageId = response.id;
            let imageDeleteDiv = document.createElement('div');
            imageDeleteDiv.id = 'image-options-' + response.id;
            imageDeleteDiv.className = 'options null';
            let imageDeleteA = document.createElement('a');
            imageDeleteA.href = '#';
            imageDeleteA.className = 'image-delete';
            imageDeleteA.innerHTML = '&#10006;';
            imageDeleteA.addEventListener('click', e => deleteImage(e, imageDeleteA).then());
            imageDeleteDiv.appendChild(imageDeleteA);
            newImgDiv.appendChild(imageDeleteDiv);
            addMouseListenerToImageInImagesSection(newImgDiv);
          }
        },
        () => {}
      );
    }
  });
});

document.querySelectorAll('form#form-user-creation').forEach(f => {
  f.addEventListener('submit', e => {
    e.preventDefault();

    const userIdEl = document.querySelector('input#userId');
    const nameEl = document.querySelector('input#name');
    const emailEl = document.querySelector('input#email');
    const bioEl = document.querySelector('textarea#bio');
    const languageIdEl = document.querySelector('select#languageId');
    const roleIdEl = document.querySelector('select#roleId');
    const timezoneEl = document.querySelector('select#timezone');
    const isEnabledEl = document.querySelector('.dropdown-menu-option[data-checked="1"]');
    const isEnabled = Number.parseInt(isEnabledEl.dataset.value) > 0;

    const userId = userIdEl && userIdEl.value ? Number.parseInt(userIdEl.value) : null;

    const payload = JSON.stringify({
      'name': nameEl.value ?? null,
      'email': emailEl.value ?? null,
      'bio': bioEl.value ?? null,
      'languageId': languageIdEl.value ?? null,
      'roleId': roleIdEl.value ?? null,
      'timezone': timezoneEl.value ?? null,
      'isEnabled': isEnabled
    });

    if (userId) {
      xhr.put('/back/user/' + userId, payload, feedbackDiv)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        });
    } else {
      xhr.post('/back/user', payload, feedbackDiv)
        .then((response) => {
          if (response.redirect) {
            window.location = response.redirect;
          }
        });
    }
  });
});

document.querySelectorAll('.article-status-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('.dropdown-menu-label').forEach(d => {
      if (op.dataset.articleStatusId === '1') {
        d.classList.add('feedback-success');
        d.classList.remove('background-light-color');
      } else {
        d.classList.remove('feedback-success');
        d.classList.add('background-light-color');
      }
    });

    document.querySelectorAll('.dropdown-menu-label > span').forEach(sp => {
      sp.textContent = op.textContent;
    });

    document.querySelectorAll('.dropdown-menu').forEach(d => {
      d.checked = false;
    });

    document.querySelectorAll('.article-status-option').forEach(o => {
      o.dataset.checked = o.dataset.articleStatusId === op.dataset.articleStatusId
        ? '1'
        : '0';
    });
  });
});

document.querySelectorAll('.user-enabled-option').forEach(op => {
  op.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('.dropdown-menu-label').forEach(d => {
      if (op.dataset.value === '1') {
        d.classList.add('feedback-success');
        d.classList.remove('background-light-color');
      } else {
        d.classList.remove('feedback-success');
        d.classList.add('background-light-color');
      }
    });

    document.querySelectorAll('.dropdown-menu-label > span').forEach(sp => {
      sp.textContent = op.textContent;
    });

    document.querySelectorAll('.dropdown-menu').forEach(d => {
      d.checked = false;
    });

    document.querySelectorAll('.user-enabled-option').forEach(o => {
      o.dataset.checked = o.dataset.value === op.dataset.value
        ? '1'
        : '0';
    });
  });
});

document.querySelectorAll('a.article-settings').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelectorAll('input[name="tags"]').forEach(i => i.value = '');

    const sideNav = document.getElementById('side-options');
    sideNav.classList.remove('null');
    sideNav.classList.add('side-options-open');

    if (!globalTags.length) {
      const searchResultEl = document.querySelector('#search-results-tags');
      xhr.get('/back/tag')
        .then(response => {
          globalTags = response.tags;
          globalTags.forEach(tag => {
            let newTag = document.createElement('span');
            newTag.className = 'result-item';
            newTag.dataset.tagId = tag.id;
            newTag.dataset.tagName = tag.name;
            newTag.textContent = tag.name;
            newTag.addEventListener('click', () => handleSearchResultClick(tag.id, tag.name, tag.name));
            searchResultEl.appendChild(newTag);
          });
        });
    }
  });
});

document.querySelectorAll('a.close-button').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();

    const sideNav = document.getElementById('side-options');
    sideNav.classList.add('null');
    sideNav.classList.remove('side-options-open');
  });
});

const handleRemoveArticleTag = (event) => {
  event.target.parentNode.parentNode.removeChild(event.target.parentNode);
};

const handleSearchResultClick = (tagId, tagName, tagInnerHtml, tagElement = null) => {
  const tags = document.querySelector('#tags-selected');
  const allResults = document.querySelectorAll('.search-results > .result-item');

  const generateNewTagHtml = function(id, name, html) {
    let imgClose = new Image();
    imgClose.className = 'img-svg m-l-05';
    imgClose.title = global.get('globalRemove');
    imgClose.alt = global.get('globalRemove');
    imgClose.src = '/img/svg/x.svg';
    imgClose.addEventListener('click', (e) => handleRemoveArticleTag(e));

    let newTag = document.createElement('span');
    newTag.className = 'result-selected';
    newTag.dataset.tagId = id;
    newTag.dataset.tagName = name;
    newTag.innerHTML = html;
    newTag.appendChild(imgClose);
    tags.appendChild(newTag);
  }

  document.querySelector('#search-results-tags').classList.add('null');
  document.querySelector('input[name="tags"]').value = '';
  allResults.forEach(r => r.classList.remove('null'));
  tagId = Number.parseInt(tagId);

  if (tagId) {
    let existingTag = [];
    document.querySelectorAll('#tags-selected > .result-selected')
      .forEach(s => {
        if (tagId === Number.parseInt(s.dataset.tagId)) {
          s.classList.add('highlight-effect');
          setTimeout(() => s.classList.remove('highlight-effect'), 2000);
        }
        existingTag.push(Number.parseInt(s.dataset.tagId));
      });

    if (existingTag.includes(tagId)) {
      return;
    }

    generateNewTagHtml(tagId, tagName, tagInnerHtml);
    return;
  }

  xhr.post('/back/tag', JSON.stringify({name: tagName}))
    .then(res => {
      if (tagElement) {
        tagElement.parentNode.removeChild(tagElement);
      }
      generateNewTagHtml(res.id, tagName, tagInnerHtml);
    });
};

document.querySelectorAll('input[name="tags"]').forEach(el => {
  const searchResultEl = document.querySelector('#search-results-tags');

  el.addEventListener('keyup', (e) => {
    e.preventDefault();

    searchResultEl.classList.remove('null');

    if (e.keyCode === 37 || e.keyCode === 39) { // left/right arrows
      return;
    }

    const allResults = document.querySelectorAll('.search-results > .result-item');

    let count = allResults.length;
    const inputText = e.target.value.trim();
    const cleanInput = cleanString(inputText);
    allResults.forEach(r => {
      if (r.dataset.new) {
        r.parentNode.removeChild(r);
        count--;
        return;
      }

      if (cleanString(r.textContent).includes(cleanInput)) {
        r.classList.remove('null');
      } else {
        count--;
        r.classList.add('null');
      }
    });

    if (!count) {
      let newTag = document.createElement('span');
      newTag.className = 'result-item';
      newTag.dataset.tagName = inputText;
      newTag.dataset.new = '1';
      newTag.innerHTML = '<span class="new">New</span>' + inputText;
      newTag.addEventListener('click', () => {
        handleSearchResultClick(null, inputText, newTag.innerHTML, newTag);
      });
      searchResultEl.appendChild(newTag);
    }
  });

  el.addEventListener('focus', () => searchResultEl.classList.remove('null'));
});

document.querySelectorAll('a.search-results-close').forEach(el => {
  el.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelector('#search-results-tags').classList.add('null');
  });
});

document.querySelectorAll('.tag-result-selected-delete').forEach(i => {
  i.addEventListener('click', (e) => handleRemoveArticleTag(e));
});

document.querySelectorAll('.pexego-rearrange-sections-button').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelector('.pexego-actions-amora').classList.add('null');
    document.querySelector('.pexego-rearrange-sections-close').classList.remove('null');
    document.querySelectorAll('.pexego-section-controls').forEach(d => {
      d.classList.remove('null');
    });
  });
});

document.querySelectorAll('.pexego-rearrange-sections-close').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelector('.pexego-actions-amora').classList.remove('null');
    document.querySelector('.pexego-rearrange-sections-close').classList.add('null');
    document.querySelectorAll('.pexego-section-controls').forEach(d => {
      d.classList.add('null');
    });
  });
});
