class Global {
  constructor() {
    this.locale = document.documentElement.lang
      ? document.documentElement.lang.toLowerCase().trim()
      : 'en';

    console.log(document.documentElement.lang);
    console.log(this.locale);

    if (['en', 'es', 'gl'].indexOf(this.locale) < 0) {
      this.locale = 'en';
    }

    console.log(this.locale);
  };

  values = {
    "en": {
      "genericError": "Something went wrong, please try again",

      "articleSectionRemove": "Remove from article",
      "articleSectionMoveUp": "Move Up",
      "articleSection:MoveDown": "Move Down",

      "editorBold": "Bold",
      "editorItalic": "Italic",
      "editorUnderline": "Underline",
      "editorStrikeThrough": "Strike-through",
      "editorHeading1": "Heading 1",
      "editorHeading2": "Heading 2",
      "editorHeading3": "Heading 3",
      "editorParagraph": "Paragraph",
      "editorQuote": "Quote",
      "editorOrderedList": "Ordered List",
      "editorUnorderedList": "Unordered List",
      "editorCode": "Code",
      "editorInsertHorizontalLine": "Insert Horizontal Line",
      "editorInsertLink": "Insert Link",
      "editorInsertImage": "Insert Image",
      "editorClearFormat": "Clear Format"
    },
    "es": {
      "genericError": "Ha ocurrido un error inesperado, por favor inténtalo de nuevo",

      "articleSectionRemove": "Eliminar sección del artículo",
      "articleSectionMoveUp": "Mover para arriba",
      "articleSection:MoveDown": "Mover para abajo",

      "editorBold": "Negrita",
      "editorItalic": "Cursiva",
      "editorUnderline": "Subrayado",
      "editorStrikeThrough": "Tachado",
      "editorHeading1": "Título 1",
      "editorHeading2": "Título 2",
      "editorHeading3": "Título 3",
      "editorParagraph": "Párrafo",
      "editorQuote": "Cita",
      "editorOrderedList": "Lista",
      "editorUnorderedList": "Lista numérica",
      "editorCode": "Código",
      "editorInsertHorizontalLine": "Insertar línea horizontal",
      "editorInsertLink": "Insertar enlace",
      "editorInsertImage": "Insertar imagen",
      "editorClearFormat": "Limpiar formato"
    },
    "gl": {
      "genericError": "Algo non foi ben, por favor inténtao de novo",

      "articleSectionRemove": "Eliminar do artigo",
      "articleSectionMoveUp": "Mover para arriba",
      "articleSection:MoveDown": "Move para abaixo",

      "editorBold": "Letra grosa",
      "editorItalic": "Cursiva",
      "editorUnderline": "Subliñado",
      "editorStrikeThrough": "Riscado",
      "editorHeading1": "Título 1",
      "editorHeading2": "Título 2",
      "editorHeading3": "Título 3",
      "editorParagraph": "Páragrafo",
      "editorQuote": "Cita",
      "editorOrderedList": "Lista",
      "editorUnorderedList": "Lista numérica",
      "editorCode": "Código",
      "editorInsertHorizontalLine": "Inserir liña horizontal",
      "editorInsertLink": "Inserir ligazón",
      "editorInsertImage": "Inserir imaxe",
      "editorClearFormat": "Limpar formato"
    }
  }

  get(key) {
    return this.values[this.locale][key] ?? '';
  }
}

export const global = new Global();
