class Global {
  constructor() {
    this.locale = document.documentElement.lang ?? 'en';
    this.locale = this.locale.toLowerCase().trim();
    this.load().then();
  }

  async load() {
    if (this.values) {
      return;
    }

    this.values = await fetch('/js/module/localisation/' + this.locale + '.json')
      .then(response => response.json());
  }

  get(key) {
    return this.values[key] ?? '';
  }
}

export const global = new Global();
