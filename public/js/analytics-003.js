function buildRedirectUrl(period, newDate) {
  const params = new URLSearchParams(window.location.search);

  let redirectUrl = window.location.origin + window.location.pathname
      + '?period=' + period
      + '&date=' + newDate;

  if (params.get('eventTypeId')) {
    redirectUrl += '&eventTypeId=' + params.get('eventTypeId');
  }

  if (params.get('itemsCount')) {
    redirectUrl += '&itemsCount=' + params.get('itemsCount');
  }

  return redirectUrl;
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
