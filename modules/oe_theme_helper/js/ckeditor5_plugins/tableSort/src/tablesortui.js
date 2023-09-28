import { Plugin } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';
import tableSortIcon from '../../../icons/table-sort.svg';

export default class TableSortUi extends Plugin {

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'TableSortUi';
  }

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;

    editor.ui.componentFactory.add('toggleTableColumnSort', locale => {
      const command = editor.commands.get('toggleTableColumnSort');
      const view = new ButtonView(locale);

      view.set({
        icon: tableSortIcon,
        tooltip: true,
        isToggleable: true
      });

      view.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');
      view.bind('label').to(command, 'value', value => value ? Drupal.t('Toggle column sort off') : Drupal.t('Toggle column sort on'));

      this.listenTo(view, 'execute', () => {
        editor.execute('toggleTableColumnSort');
        editor.editing.view.focus();
      });

      return view;
    });
  }

}

