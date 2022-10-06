import {global} from './localisation-001.js';
import {pexegoClasses} from "./Pexego-002.js";

class UtilClass {
  getUpdatedAtTime() {
    const now = new Date();
    const prefix = ' ' + global.get('globalAt') + ' ';

    return prefix + now.getHours().toString().padStart(2, '0') +
      ':' + now.getMinutes().toString().padStart(2, '0');
  }

  cleanString(text) {
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
  }

  getSectionTypeIdFromClassList(classList) {
    if (classList.contains(pexegoClasses.sectionParagraph)) {
      return 1;
    }

    if (classList.contains(pexegoClasses.sectionImage)) {
      return 2;
    }

    if (classList.contains(pexegoClasses.sectionVideo)) {
      return 3;
    }
  }
}

export const Util = new UtilClass();
