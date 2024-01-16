import { Command } from 'ckeditor5/src/core';
import { getSelectionAffectedTable } from '@ckeditor/ckeditor5-table/src/tablecaption/utils';

export default class ToggleTableZebraStripingCommand extends Command {

  /**
   * @inheritDoc
   */
  refresh() {
    const editor = this.editor;
    const tableElement = getSelectionAffectedTable(editor.model.document.selection);

    this.isEnabled = !!tableElement;
    this.value = this.isEnabled && tableElement.hasAttribute('zebraStriping');
  }

  /**
   * @inheritDoc
   */
  execute() {
    const model = this.editor.model;
    const tableElement = getSelectionAffectedTable( model.document.selection );

    model.change((writer) => {
      if (this.value) {
        writer.removeAttribute('zebraStriping', tableElement);
      }
      else {
        writer.setAttribute('zebraStriping', 'true', tableElement);
      }
    });
  }

}
