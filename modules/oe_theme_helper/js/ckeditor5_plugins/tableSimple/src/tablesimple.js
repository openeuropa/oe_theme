import { Plugin } from 'ckeditor5/src/core';
import TableSimpleEditing from "./tablesimpleediting";
import TableSimpleUi from "./tablesimpleui";

export default class TableSimple extends Plugin {

  /**
   * @inheritdoc
   */
  static get requires() {
    return [TableSimpleEditing, TableSimpleUi];
  }

}
