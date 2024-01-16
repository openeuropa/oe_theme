import { Plugin } from 'ckeditor5/src/core';
import ToggleTableColumnSortCommand from "./tablecolumnsortcommand";

export default class TableSortEditing extends Plugin {

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;
    const model = editor.model;
    const schema = model.schema;
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

    const document = model.document;
    // Remove sortable attributes when all table heading rows are removed.
    document.registerPostFixer(writer => {
      const changes = document.differ.getChanges();

      for (const entry of changes) {
        const {type, attributeKey, attributeNewValue} = entry;

        if (type !== 'attribute' || attributeKey !== 'headingRows' || !!attributeNewValue) {
          continue;
        }

        const parent = entry.range.start.nodeAfter;
        if (!parent || !parent.is('element', 'table')) {
          continue;
        }

        for (const node of model.createRangeOn(parent).getItems()) {
          if (node.is('element', 'tableCell')) {
            writer.removeAttribute('sortable', node);
          }
        }
      }

      return false;
    });
  }

}
