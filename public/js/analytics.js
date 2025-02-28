function buildRedirectUrl(
  period = null,
  newDate = null,
  parameterId = null,
  eventId = null,
) {
  const queryParams = new URLSearchParams(window.location.search);

  if (period) {
    queryParams.set('period', period);
  }

  if (newDate) {
    queryParams.set('date', newDate);
  }

  if (parameterId === 'NULL') {
    queryParams.delete('paramId');
  } else if (parameterId) {
    queryParams.set('paramId', parameterId);
  }

  if (eventId === 'NULL') {
    queryParams.delete('eventId');
  } else if (eventId) {
    queryParams.set('eventId', eventId);
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

document.querySelectorAll('.analytics-event-js').forEach(s => {
  s.addEventListener('click', (e) => {
    e.preventDefault();

    const paramId = s.dataset.parameterId;
    const eventId = s.dataset.eventId;

    window.location = buildRedirectUrl(null, null, paramId, eventId);
  });
});

document.querySelectorAll('.analytics-close-js').forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();

    window.location = buildRedirectUrl(null, null, 'NULL', 'NULL');
  });
});
