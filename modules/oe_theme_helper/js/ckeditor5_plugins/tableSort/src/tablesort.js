import { Plugin } from "ckeditor5/src/core";
import TableSortEditing from "./tablesortediting";
import TableSortUi from "./tablesortui";

export default class TableSort extends Plugin {

  /**
   * @inheritdoc
   */
  static get requires() {
    return [TableSortEditing, TableSortUi];
  }

};
