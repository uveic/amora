import {global} from './localisation.js';

function getUpdatedAtTime () {
  const now = new Date();
  const prefix = ' ' + global.get('globalAt') + ' ';

  return prefix + now.getHours().toString().padStart(2, '0') +
    ':' + now.getMinutes().toString().padStart(2, '0');
}

function cleanTextForUrl(text) {
  return text
    .trim()
    .toLowerCase()
    .replace(/á/g, 'a')
    .replace(/é/g, 'e')
    .replace(/í/g, 'i')
    .replace(/ó/g, 'o')
    .replace(/ú/g, 'u')
    .replace(/ñ/g, 'n')
    .replace(/\s{2,}/g,' ')
    .replace(/\s/g,'-')
    .replace(/[^A-Za-z0-9\-]/g,'-');
}

export {getUpdatedAtTime, cleanTextForUrl};
