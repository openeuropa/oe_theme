import { Plugin } from 'ckeditor5/src/core';
import TableZebraStripingUi from "./tablezebrastripingui";
import TableZebraStripingEditing from "./tablezebrastripingediting";

export default class TableZebraStriping extends Plugin {

  /**
   * @inheritdoc
   */
  static get requires() {
    return [TableZebraStripingEditing, TableZebraStripingUi];
  }

}
