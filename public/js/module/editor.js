const defaultParagraphSeparatorString = 'defaultParagraphSeparator';
const formatBlock = 'formatBlock';
const addEventListener = (parent, type, listener) => parent.addEventListener(type, listener);
const appendChild = (parent, child) => parent.appendChild(child);
const createElement = (tag) => document.createElement(tag);
const queryCommandState = (command) => document.queryCommandState(command);
const queryCommandValue = (command) => document.queryCommandValue(command);

const exec = (command, value = null) => document.execCommand(command, false, value);

const defaultActions = {
  bold: {
    icon: '<b>B</b>',
    title: 'Bold',
    state: () => queryCommandState('bold'),
    result: () => exec('bold')
  },
  italic: {
    icon: '<i>I</i>',
    title: 'Italic',
    state: () => queryCommandState('italic'),
    result: () => exec('italic')
  },
  underline: {
    icon: '<u>U</u>',
    title: 'Underline',
    state: () => queryCommandState('underline'),
    result: () => exec('underline')
  },
  strikethrough: {
    icon: '<del>S</del>',
    title: 'Strike-through',
    state: () => queryCommandState('strikeThrough'),
    result: () => exec('strikeThrough')
  },
  heading1: {
    icon: '<b>H1</b>',
    title: 'Heading 1',
    result: () => exec(formatBlock, '<h1>')
  },
  heading2: {
    icon: '<b>H2</b>',
    title: 'Heading 2',
    result: () => exec(formatBlock, '<h2>')
  },
  paragraph: {
    icon: '&#182;',
    title: 'Paragraph',
    result: () => exec(formatBlock, '<p>')
  },
  quote: {
    icon: '&#8220; &#8221;',
    title: 'Quote',
    result: () => exec(formatBlock, '<blockquote>')
  },
  olist: {
    icon: '&#35;',
    title: 'Ordered List',
    result: () => exec('insertOrderedList')
  },
  ulist: {
    icon: '&#8226;',
    title: 'Unordered List',
    result: () => exec('insertUnorderedList')
  },
  code: {
    icon: '&lt;/&gt;',
    title: 'Code',
    result: () => exec(formatBlock, '<pre>')
  },
  line: {
    icon: '&#8213;',
    title: 'Horizontal Line',
    result: () => exec('insertHorizontalRule')
  },
  link: {
    icon: '<img src="/img/svg/link.svg" class="img-svg m-t-0" alt="Insert link">',
    title: 'Insert link',
    result: () => {
      const url = window.prompt('Enter the link URL')
      if (url) exec('createLink', url)
    }
  },
  image: {
    icon: '&#128247;',
    title: 'Image',
    result: () => {
      const url = window.prompt('Enter the image URL')
      if (url) exec('insertImage', url)
    }
  },
  eraser: {
    icon: '✕',
    title: 'Clear Format',
    result: () => {
      exec('removeFormat');
    }
  }
};

const defaultClasses = {
  actionbar: 'pell-actionbar',
  actionbarLeft: 'pell-actionbar-left',
  actionbarRight: 'pell-actionbar-right',
  button: 'pell-button',
  content: 'pell-content',
  selected: 'pell-button-selected'
};

const init = function(settings) {
  const actions = settings.actions
    ? (
      settings.actions.map(action => {
        if (typeof action === 'string') {
          return defaultActions[action];
        } else if (defaultActions[action.name]) {
          return { ...defaultActions[action.name], ...action };
        }
        return action;
      })
    )
    : Object.keys(defaultActions).map(action => defaultActions[action]);

  const classes = { ...defaultClasses, ...settings.classes };

  const defaultParagraphSeparator = settings[defaultParagraphSeparatorString] || 'div';

  const actionbarWrapper = createElement('div');
  actionbarWrapper.className = classes.actionbar;

  const actionbarLeft = createElement('div');
  actionbarLeft.className = classes.actionbarLeft;
  appendChild(actionbarWrapper, actionbarLeft);
  const actionbarRight = createElement('div');
  actionbarRight.className = classes.actionbarRight;
  appendChild(actionbarWrapper, actionbarRight);
  appendChild(settings.element, actionbarWrapper);

  let content = document.querySelector('div.' + classes.editorId);
  if (!content) {
    content = settings.element.content = createElement('div');
    content.contentEditable = true;
    content.className = classes.content;
  }

  content.oninput = ({target: {firstChild}}) => {
    if (firstChild && firstChild.nodeType === 3) {
      exec(formatBlock, `<${defaultParagraphSeparator}>`);
    } else if (content.innerHTML === '<br>') {
      content.innerHTML = '';
    }
    settings.onChange(content.innerHTML);
  };

  content.onkeydown = (event) => {
    if (event.key === 'Enter' && queryCommandValue(formatBlock) === 'blockquote') {
      setTimeout(() => exec(formatBlock, `<${defaultParagraphSeparator}>`), 0);
    }
  };
  appendChild(settings.element, content);

  actions.forEach(action => {
    const button = createElement('button');
    button.className = classes.button;
    button.innerHTML = action.icon;
    button.title = action.title;
    button.setAttribute('type', 'button');
    button.onclick = (e) => action.result(e) && content.focus();

    if (action.state) {
      const handler = () => button.classList[action.state() ? 'add' : 'remove'](classes.selected);
      addEventListener(content, 'keyup', handler);
      addEventListener(content, 'mouseup', handler);
      addEventListener(button, 'click', handler);
    }

    action.positionRight
      ? appendChild(actionbarRight, button)
      : appendChild(actionbarLeft, button);
  });

  exec(defaultParagraphSeparatorString, defaultParagraphSeparator);

  return settings.element;
};

function loadEditor(elementId, elementHtmlId = null) {
  elementHtmlId = elementHtmlId ?? elementId + '-html';

  init({
    element: document.querySelector('#' + elementId),
    onChange: (html) => {document.querySelector('#' + elementHtmlId).textContent = html},
    defaultParagraphSeparator: 'p',
    actions: [
      'bold',
      'italic',
      'underline',
      'strikethrough',
      'olist',
      'ulist',
      'heading1',
      'heading2',
      'paragraph',
      'link',
      'eraser'
    ],
    classes: {
      editorId: elementId
    }
  });
}

export {
  loadEditor
};
