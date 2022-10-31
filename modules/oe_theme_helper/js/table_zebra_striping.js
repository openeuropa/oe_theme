/**
 * @file
 * Plugin for adding zebra striping of table.
 */

(function ($, CKEDITOR) {
  "use strict";

  CKEDITOR.plugins.add('table_zebra_striping', {
    afterInit: function afterInit (editor) {
      CKEDITOR.on('dialogDefinition', function(event) {
        const dialog_name = event.data.name;

        if (dialog_name !== 'table' && dialog_name !== 'tableProperties') {
          return;
        }

        const dialog_definition = event.data.definition;
        const info_tab = dialog_definition.getContents('info');
        const zebra_attribute = 'data-striped';
        // Avoid multiple checkbox adding.
        if (!info_tab.get('zebraStriping')) {
          info_tab.add({
            type: 'checkbox',
            label: event.editor.config.zebra_striping__checkboxLabel,
            id: 'zebraStriping',
            labelStyle: 'display: inline;',
            requiredContent: 'table[' + zebra_attribute + ']',
            setup: function (selectedTable) {
              this.setValue(selectedTable.getAttribute(zebra_attribute));
            },
            commit: function (data, selectedTable) {
              if (this.getValue()) {
                selectedTable.setAttribute(zebra_attribute, true);
              } else {
                selectedTable.removeAttribute(zebra_attribute);
              }
            }
          });
        }
      });
    }
  });

})(jQuery, CKEDITOR);
