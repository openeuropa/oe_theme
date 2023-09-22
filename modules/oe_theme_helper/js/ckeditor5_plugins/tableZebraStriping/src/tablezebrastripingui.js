import { Plugin } from 'ckeditor5/src/core';
import { icons } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';

export default class TableZebraStripingUi extends Plugin {

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'TableZebraStripingUi';
  }

  init() {
    const editor = this.editor;

    editor.ui.componentFactory.add('toggleTableZebraStriping', locale => {
      const command = editor.commands.get('toggleTableZebraStriping');
      const view = new ButtonView(locale);

      view.set({
        icon: icons.cancel,
        tooltip: true,
        isToggleable: true
      });

      view.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');
      view.bind('label').to(command, 'value', value => value ? Drupal.t('Toggle zebra striping off') : Drupal.t('Toggle zebra striping on'));

      this.listenTo(view, 'execute', () => {
        editor.execute('toggleTableZebraStriping');
        editor.editing.view.focus();
      });

      return view;
    });
  }

}
