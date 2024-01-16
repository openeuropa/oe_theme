import { Plugin } from 'ckeditor5/src/core';
import ToggleTableSimpleCommand from "./tablesimplecommand";

export default class TableSimpleEditing extends Plugin {

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;
    const schema = editor.model.schema;
    const conversion = editor.conversion;

    schema.extend( 'table', {
      allowAttributes: 'simpleMode'
    });

    conversion
      .for('upcast')
      .attributeToAttribute({
        view: {
          name: 'table',
          key: 'data-simple'
        },
        model: 'simpleMode',
      });
    conversion
      .for('editingDowncast')
      .attributeToAttribute({
        model: {
          name: 'table',
          key: 'simpleMode'
        },
        view: {
          key: 'class',
          value: ['table-simple']
        },
      });
    conversion
      .for('dataDowncast')
      .attributeToAttribute({
        model: {
          name: 'table',
          key: 'simpleMode'
        },
        view: 'data-simple',
      });

    editor.commands.add('toggleTableSimple', new ToggleTableSimpleCommand(editor));
  }

}
