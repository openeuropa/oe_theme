/**
 * @file
 * Plugin for adding sorting of table.
 */

(function ($, CKEDITOR) {
  "use strict";

  CKEDITOR.plugins.add('table_sort', {
    afterInit: function afterInit (editor) {
      CKEDITOR.on('dialogDefinition', function(event) {
        const dialog_name = event.data.name;
        const sort_attribute = 'data-sortable';

        if (dialog_name !== 'cellProperties') {
          return;
        }

        const dialog_definition = event.data.definition;
        const info_tab = dialog_definition.getContents('info');
        const langCell = event.editor.lang.table.cell;
        // Avoid multiple selectbox adding.
        if (!info_tab.get('columnSortable')) {
          info_tab.add({
            type: 'select',
            label: 'Sortable',
            id: 'columnSortable',
            requiredContent: 'th[' + sort_attribute + ']',
            items: [
              [langCell.yes, 'yes'],
              [langCell.no, 'no'],
            ],

            /**
             * @param {CKEDITOR.dom.element[]} selectedCells
             */
            setup: function (selectedCells) {
              // Disable the element if any of the selected cells is not a header cell.
              for (let i = 0; i < selectedCells.length; i++) {
                if (selectedCells[i].getName() !== 'th') {
                  this.disable();
                  this.setValue(null);
                  return;
                }
              }

              // This method receives an array of selected cells.
              // We use the value of the first one as driver for the remaining cells.
              // @see setupCells() in /plugins/tabletools/dialogs/tableCell.js
              let value = selectedCells[0].hasAttribute(sort_attribute);

              for (let i = 1; i < selectedCells.length; i++) {
                if (selectedCells[i].hasAttribute(sort_attribute) !== value) {
                  // If any of the cells has a different sortable value, set the value
                  // to undetermined.
                  value = null;
                  break;
                }
              }

              // Convert the value to the matching option.
              if (value !== null) {
                value = value ? 'yes' : 'no';
              }

              this.setValue(value);

              // The only way to have an empty select value in Firefox is
              // to set a negative selectedIndex.
              if (value === null && CKEDITOR.env.gecko) {
                this.getInputElement().$.selectedIndex = -1;
              }
            },

            /**
             * @param {CKEDITOR.dom.element} selectedCell
             */
            commit: function (selectedCell) {
              const value = this.getValue();

              // Handle only supported values.
              if (value === 'yes') {
                selectedCell.setAttribute(sort_attribute, true);
              } else if (value === 'no') {
                selectedCell.removeAttribute(sort_attribute);
              }
            }
          });
        }
      });
    }
  });

})(jQuery, CKEDITOR);
