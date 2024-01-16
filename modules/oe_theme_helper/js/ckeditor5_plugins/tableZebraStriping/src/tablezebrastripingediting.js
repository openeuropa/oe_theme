import { Plugin } from 'ckeditor5/src/core';
import ToggleTableZebraStripingCommand from "./tablezebrastripingcommand";

export default class TableZebraStripingEditing extends Plugin {

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;
    const schema = editor.model.schema;
    const conversion = editor.conversion;

    schema.extend( 'table', {
      allowAttributes: 'zebraStriping'
    });

    conversion
      .for('upcast')
      .attributeToAttribute({
        view: {
          name: 'table',
          key: 'data-striped'
        },
        model: 'zebraStriping',
      });
    conversion
      .for('editingDowncast')
      .attributeToAttribute({
        model: {
          name: 'table',
          key: 'zebraStriping'
        },
        view: {
          key: 'class',
          value: ['table-zebra-striped']
        },
      });
    conversion
      .for('dataDowncast')
      .attributeToAttribute({
        model: {
          name: 'table',
          key: 'zebraStriping'
        },
        view: 'data-striped',
      });

    editor.commands.add('toggleTableZebraStriping', new ToggleTableZebraStripingCommand(editor));
  }

}
