import { Plugin } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';
import tableSimpleIcon from './../../../icons/table-simple.svg';

export default class TableSimpleUi extends Plugin {

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'TableSimpleUi';
  }

  init() {
    const editor = this.editor;

    editor.ui.componentFactory.add('toggleTableSimple', locale => {
      const command = editor.commands.get('toggleTableSimple');
      const view = new ButtonView(locale);

      view.set({
        icon: tableSimpleIcon,
        tooltip: true,
        isToggleable: true
      });

      view.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');
      view.bind('label').to(command, 'value', value => value ? Drupal.t('Toggle simple mode off') : Drupal.t('Toggle simple mode on'));

      this.listenTo(view, 'execute', () => {
        editor.execute('toggleTableSimple');
        editor.editing.view.focus();
      });

      return view;
    });
  }

}
