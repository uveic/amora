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

function getYoutubeVideoIdFromUrl(url) {
  const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
  const match = url.match(regExp);
  return match && match[7].length === 11 ? match[7] : false;
}

export {getUpdatedAtTime, cleanTextForUrl, getYoutubeVideoIdFromUrl};
