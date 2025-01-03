import {Util} from "./module/Util.js?v=000";

function handleDropdownOptionClick(event) {
  event.preventDefault();
  const elementOption = event.currentTarget;
  const dropDownIdentifier = elementOption.dataset.dropdownIdentifier;
  const elementLabel = document.querySelector('#' + dropDownIdentifier + '-dd-label');
  const elementCheckbox = document.querySelector('#' + dropDownIdentifier + '-dd-checkbox');
  const optionClassName = dropDownIdentifier + '-dd-option';

  elementLabel.classList.forEach(cl => {
    if (cl.startsWith('status-')) {
      elementLabel.classList.remove(cl);
    }
  });

  const newClassName = Array.from(elementOption.classList).filter(cl => cl.startsWith('status-') === true)[0] ?? null;
  if (newClassName) {
    elementLabel.classList.add(newClassName);
  }
  elementLabel.querySelector('span').innerHTML = elementOption.innerHTML;
  elementCheckbox.checked = false;

  document.querySelectorAll('.' + optionClassName).forEach(o => {
    o.dataset.checked = o.dataset.value === elementOption.dataset.value ? '1' : '0';
  });
}

window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.dropdown-menu-option').forEach(op => {
    op.addEventListener('click', handleDropdownOptionClick);
  });

  document.querySelectorAll('.filter-close').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelector('.filter-container').classList.add('null');
    });
  });

  document.querySelectorAll('.filter-open').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelector('.filter-container').classList.remove('null');
    });
  });

  document.querySelectorAll('.modal-open-js').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      document.body.style.overflow = 'hidden';
    });
  });

  document.querySelectorAll('.modal-close-button').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      document.body.style.overflow = 'auto';
      el.parentElement.parentElement.classList.add('null');
    });
  });

  document.querySelectorAll('.copy-link').forEach(a => {
    a.addEventListener('click', e => Util.handleCopyLink(e, a.href));
  });
});

export {handleDropdownOptionClick};