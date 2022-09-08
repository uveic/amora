import {global} from './localisation.js';
import {PexegoEditor as Pexego} from "./Pexego.js";

const getUpdatedAtTime = () => {
  const now = new Date();
  const prefix = ' ' + global.get('globalAt') + ' ';

  return prefix + now.getHours().toString().padStart(2, '0') +
    ':' + now.getMinutes().toString().padStart(2, '0');
};

const cleanString = (text) => {
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
    .replace(/[^A-Za-z0-9\-]/g,'-')
    .replace(/-+/g, '-')
    .replace(/-$/g, '');
};

const getSectionTypeIdFromClassList = (classList) => {
  if (classList.contains(Pexego.classes.sectionParagraph)) {
    return 1;
  }

  if (classList.contains(Pexego.classes.sectionImage)) {
    return 2;
  }

  if (classList.contains(Pexego.classes.sectionVideo)) {
    return 3;
  }
};

export {
  getSectionTypeIdFromClassList,
  getUpdatedAtTime,
  cleanString,
};
