import { Plugin } from 'ckeditor5/src/core';
import ToggleTableColumnSortCommand from "./tablecolumnsortcommand";

export default class TableSortEditing extends Plugin {

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;
    const schema = editor.model.schema;
    const conversion = editor.conversion;

    schema.extend( 'tableCell', {
      allowAttributes: 'sortable'
    });

    conversion
      .for('upcast')
      .attributeToAttribute({
        view: {
          name: 'th',
          key: 'data-sortable'
        },
        model: 'sortable',
      });
    conversion
      .for('editingDowncast')
      .attributeToAttribute({
        model: {
          name: 'tableCell',
          key: 'sortable'
        },
        view: {
          key: 'class',
          value: ['cell-sortable']
        },
      });
    conversion
      .for('dataDowncast')
      .attributeToAttribute({
        model: {
          name: 'tableCell',
          key: 'sortable'
        },
        view: 'data-sortable',
      });

    editor.commands.add('toggleTableColumnSort', new ToggleTableColumnSortCommand(editor));
  }

}
