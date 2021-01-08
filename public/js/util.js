function getUpdatedAtTime (language = 'EN') {
  const now = new Date();

  let prefix = ' at ';

  switch (language.toUpperCase()) {
    case 'GL':
      prefix = ' ás ';
      break;
    case 'ES':
      prefix = ' a las ';
      break;
  }

  return prefix + now.getHours().toString().padStart(2, '0') +
    ':' + now.getMinutes().toString().padStart(2, '0');
}

function cleanTextForUrl(text) {
  return text.trim()
    .replace(/\s{2,}/g,' ')
    .replace(/\s/g,'-')
    .replace(/[^A-Za-z0-9\-]/g,'')
    .toLowerCase();
}

export {getUpdatedAtTime, cleanTextForUrl};
