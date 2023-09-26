import { Command } from 'ckeditor5/src/core';
import { getSelectionAffectedTable } from '@ckeditor/ckeditor5-table/src/tablecaption/utils';

export default class ToggleTableSimpleCommand extends Command {

  /**
   * @inheritDoc
   */
  refresh() {
    const editor = this.editor;
    const tableElement = getSelectionAffectedTable(editor.model.document.selection);

    this.isEnabled = !!tableElement;
    if (!this.isEnabled) {
      this.value = false;
    }
    else {
      this.value = tableElement.hasAttribute('simpleMode');
    }
  }

  /**
   * @inheritDoc
   */
  execute() {
    const model = this.editor.model;
    const tableElement = getSelectionAffectedTable( model.document.selection );

    model.change((writer) => {
      if (this.value) {
        writer.removeAttribute('simpleMode', tableElement);
      }
      else {
        writer.setAttribute('simpleMode', 'true', tableElement);
      }
    });
  }

}
