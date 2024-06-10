function buildRedirectUrl(
  period,
  newDate,
  url = null,
  device = null,
  browser = null,
  countryIsoCode = null,
  languageIsoCode = null,
) {
  const queryParams = new URLSearchParams(window.location.search);

  if (period) {
    queryParams.set('period', period);
  }

  if (newDate) {
    queryParams.set('date', newDate);
  }

  if (url === 'NULL') {
    queryParams.delete('url');
  } else if (url) {
    queryParams.set('url', url);
  }

  if (device === 'NULL') {
    queryParams.delete('device');
  } else if (device) {
    queryParams.set('device', device);
  }

  if (browser === 'NULL') {
    queryParams.delete('browser');
  } else if (browser) {
    queryParams.set('browser', browser);
  }

  if (countryIsoCode === 'NULL') {
    queryParams.delete('countryIsoCode');
  } else if (countryIsoCode) {
    queryParams.set('countryIsoCode', countryIsoCode);
  }

  if (languageIsoCode === 'NULL') {
    queryParams.delete('languageIsoCode');
  } else if (languageIsoCode) {
    queryParams.set('languageIsoCode', languageIsoCode);
  }

  const redirectUrl = window.location.origin + window.location.pathname;

  if (!queryParams.entries()) {
    return redirectUrl;
  }

  return redirectUrl + (queryParams.entries() ? '?' + queryParams.toString() : '');
}

document.querySelectorAll('a.analytics-controls-previous').forEach(p => {
  p.addEventListener('click', (e) => {
    e.preventDefault();

    const controls = document.querySelector('.analytics-controls');
    const period = controls.dataset.period;
    const date = controls.dataset.date;
    const d = new Date(date + 'T00:00:00Z');

    if (period === 'day') {
      d.setDate(d.getDate() - 1);
    } else if (period === 'month') {
      d.setMonth(d.getMonth() - 1);
    } else {
      d.setFullYear(d.getFullYear() - 1);
    }

    const newDate = d.getFullYear()
      + '-' + (d.getMonth() + 1).toString().padStart(2, '0')
      + '-' + d.getDate().toString().padStart(2, '0');

    window.location = buildRedirectUrl(period, newDate);
  });
});

document.querySelectorAll('a.analytics-controls-next').forEach(n => {
  n.addEventListener('click', (e) => {
    e.preventDefault();

    const controls = document.querySelector('.analytics-controls');
    const period = controls.dataset.period;
    const date = controls.dataset.date;
    const d = new Date(date + 'T00:00:00Z');

    if (period === 'day') {
      d.setDate(d.getDate() + 1);
    } else if (period === 'month') {
      d.setMonth(d.getMonth() + 1);
    } else {
      d.setFullYear(d.getFullYear() + 1);
    }

    const newDate = d.getFullYear()
      + '-' + (d.getMonth() + 1).toString().padStart(2, '0')
      + '-' + d.getDate().toString().padStart(2, '0');

    window.location = buildRedirectUrl(period, newDate);
  });
});

document.querySelectorAll('a.analytics-controls-more').forEach(m => {
  m.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelector('.analytics-controls-more-options').classList.toggle('null');
  });
});

document.querySelectorAll('a.analytics-controls-event-type').forEach(m => {
  m.addEventListener('click', (e) => {
    e.preventDefault();

    document.querySelector('.analytics-controls-event-type-options').classList.toggle('null');
  });
});

document.querySelectorAll('.analytics-page-js').forEach(s => {
  s.addEventListener('click', (e) => {
    e.preventDefault();

    let url = s.dataset.url;
    if (!url) {
      url = 'homepage';
    }

    window.location = buildRedirectUrl(null, null, url);
  });
});

document.querySelectorAll('.analytics-device-js').forEach(s => {
  s.addEventListener('click', (e) => {
    e.preventDefault();
    window.location = buildRedirectUrl(null, null, null, s.textContent);
  });
});

document.querySelectorAll('.analytics-browser-js').forEach(s => {
  s.addEventListener('click', (e) => {
    e.preventDefault();
    window.location = buildRedirectUrl(null, null, null, null, s.textContent);
  });
});

document.querySelectorAll('.analytics-country-js').forEach(s => {
  s.addEventListener('click', (e) => {
    e.preventDefault();
    window.location = buildRedirectUrl(null, null, null, null, null, s.dataset.isoCode);
  });
});

document.querySelectorAll('.analytics-language-js').forEach(s => {
  s.addEventListener('click', (e) => {
    e.preventDefault();
    window.location = buildRedirectUrl(null, null, null, null, null, null, s.textContent);
  });
});

document.querySelectorAll('.analytics-close-js').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    window.location = buildRedirectUrl(null, null, 'NULL', 'NULL', 'NULL', 'NULL', 'NULL');
  });
});
