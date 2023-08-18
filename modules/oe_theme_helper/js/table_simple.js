/**
 * @file
 * Plugin for adding simple table mode.
 *
 * Used on mobile (with horizontal scroll) instead of ECL enhanced one.
 */

(function ($, CKEDITOR) {
  "use strict";

  CKEDITOR.plugins.add('table_simple', {
    afterInit: function afterInit (editor) {
      CKEDITOR.on('dialogDefinition', function(event) {
        const dialog_name = event.data.name;

        if (dialog_name !== 'table' && dialog_name !== 'tableProperties') {
          return;
        }

        const dialog_definition = event.data.definition;
        const info_tab = dialog_definition.getContents('info');
        const simple_attribute = 'data-simple';
        // Avoid multiple checkbox adding.
        if (!info_tab.get('simple')) {
          info_tab.add({
            type: 'checkbox',
            label: event.editor.config.simple__checkboxLabel,
            id: 'simple',
            labelStyle: 'display: inline;',
            requiredContent: 'table[' + simple_attribute + ']',
            setup: function (selectedTable) {
              this.setValue(selectedTable.getAttribute(simple_attribute));
            },
            commit: function (data, selectedTable) {
              if (this.getValue()) {
                selectedTable.setAttribute(simple_attribute, true);
              } else {
                selectedTable.removeAttribute(simple_attribute);
              }
            }
          });
        }
      });
    }
  });

})(jQuery, CKEDITOR);
