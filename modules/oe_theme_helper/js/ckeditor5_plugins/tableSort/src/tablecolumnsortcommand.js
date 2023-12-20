import { Command } from 'ckeditor5/src/core';

export default class ToggleTableColumnSortCommand extends Command {

  /**
   * @inheritDoc
   */
  refresh() {
    const tableUtils = this.editor.plugins.get('TableUtils');
    const model = this.editor.model;
    const selectedCells = tableUtils.getSelectionAffectedTableCells(model.document.selection);
    const isInTable = selectedCells.length > 0;

    this.isEnabled = isInTable && selectedCells.every(cell => this._isInFirstHeadingRow(cell, cell.parent.parent));
    this.value = this.isEnabled && selectedCells.every(cell => cell.hasAttribute('sortable'));
  }

  /**
   * @inheritDoc
   */
  execute() {
    const tableUtils = this.editor.plugins.get('TableUtils');
    const model = this.editor.model;
    const selectedCells = tableUtils.getSelectionAffectedTableCells(model.document.selection);

    model.change((writer) => {
      for (const cell of selectedCells) {
        if (this.value) {
          writer.removeAttribute('sortable', cell);
        }
        else {
          writer.setAttribute('sortable', 'true', cell);
        }
      }
    });
  }

  /**
   * Checks if a table cell is in the first heading row.
   */
  _isInFirstHeadingRow(tableCell, table) {
    const headingRows = parseInt(table.getAttribute('headingRows') || '0');
    return !!headingRows && tableCell.parent.index === 0;
  }

}
