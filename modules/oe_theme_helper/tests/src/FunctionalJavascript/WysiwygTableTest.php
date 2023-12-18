<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\ckeditor\Traits\CKEditorTestTrait;
use Drupal\user\RoleInterface;

/**
 * Test the table in WYSIWYG.
 *
 * @group batch3
 *
 * @group oe_theme_helper
 */
class WysiwygTableTest extends WebDriverTestBase {

  use CKEditorTestTrait;

  /**
   * Disabled until FRONT-4076 is fixed.
   *
   * {@inheritdoc}
   */
  protected $failOnJavascriptConsoleErrors = FALSE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_theme_helper',
    'oe_theme_content_page',
    'ckeditor',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme', 'claro']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();

    // Create a text format and associate this with CKEditor.
    FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
      'weight' => 1,
      'roles' => [RoleInterface::AUTHENTICATED_ID],
      'filters' => [
        'filter_ecl_table' => [
          'status' => 1,
        ],
      ],
    ])->save();

    Editor::create([
      'format' => 'full_html',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [
            0 => [
              0 => [
                'name' => 'Group with table',
                'items' => [
                  'Table',
                  'Source',
                ],
              ],
            ],
          ],
        ],
      ],
    ])->save();

    $this->webUser = $this->drupalCreateUser([
      'access administration pages',
      'create oe_page content',
      'edit own oe_page content',
      'view the administration theme',
    ]);
  }

  /**
   * Test table widget in WYSIWYG.
   */
  public function testWysiwygTable(): void {
    $this->markTestSkipped('Must be re-enabled before considering migration to ECL 4 as complete.');
    $web_assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalLogin($this->webUser);
    // "Zebra striping" checkbox should be visible in Table properties dialog.
    $this->drupalGet('node/add/oe_page');
    $this->waitForEditor();
    $this->waitOnCkeditorInstance('edit-body-0-value');
    $this->pressEditorButton('table');
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $this->assertSession()->waitForElementVisible('css', '.cke_editor_edit-body-0-value_dialog .cke_dialog_ui_checkbox');
    $checkboxes = $this->getSession()->getPage()->findAll('css', '.cke_editor_edit-body-0-value_dialog .cke_dialog_ui_checkbox');
    $this->assertEquals('Simple table', $checkboxes[0]->getText());
    $this->assertEquals('Zebra striping', $checkboxes[1]->getText());
    // "Zebra striping" and "Simple table" checkboxes should not be visible in
    // Table properties dialog with disabled ECL table filter.
    FilterFormat::load('full_html')
      ->setFilterConfig('filter_ecl_table', ['status' => FALSE])
      ->save();
    $this->getSession()->reload();
    $this->waitForEditor();
    $this->waitOnCkeditorInstance('edit-body-0-value');
    $this->pressEditorButton('table');
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $web_assert->elementNotExists('css', '.cke_editor_edit-body-0-value_dialog .cke_dialog_ui_checkbox');
    // "Zebra striping" and "Simple table" checkboxes should be visible in
    // Table properties dialog after enabling ECL table filter.
    FilterFormat::load('full_html')
      ->setFilterConfig('filter_ecl_table', ['status' => TRUE])
      ->save();
    $this->getSession()->reload();
    $this->waitForEditor();
    $this->waitOnCkeditorInstance('edit-body-0-value');
    $this->pressEditorButton('table');
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $dialog = $this->getSession()->getPage()->find('css', '.cke_editor_edit-body-0-value_dialog');
    $dialog->findField('Zebra striping')->click();
    $dialog->findField('Simple table')->click();
    $page->pressButton('OK');
    // Data attribute should be present after enabling "Zebra striping" and
    // "Simple table" options.
    $this->pressEditorButton('source');
    $this->assertStringContainsString('<table border="1" cellpadding="1" cellspacing="1" data-simple="true" data-striped="true"', $page->find('css', 'textarea.cke_source')
      ->getValue());
    $this->pressEditorButton('source');
    // Assert enabled "Zebra striping" and "Simple table" checkboxes in Table
    // properties dialog.
    $this->assignNameToCkeditorIframe('edit-body-0-value-instance-id');
    $this->getSession()->switchToIFrame('edit-body-0-value-instance-id');
    $page->find('css', 'table > tbody > tr > td')->rightClick();
    $this->getSession()->switchToIFrame();
    $this->getSession()->switchToIFrame($page->find('css', '.cke_menu_panel iframe')->getAttribute('id'));
    $this->clickLink('Table Properties');
    $this->getSession()->switchToIFrame();
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $this->assertTrue($page->findField('Zebra striping')->isChecked());
    $this->assertTrue($page->findField('Simple table')->isChecked());
    // Enable first row headers for table.
    $this->getOpenedDialogElement()->selectFieldOption('Headers', 'First Row');
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');
    // Put content for table headers.
    $javascript = <<<JS
(function(){
  var editor = CKEDITOR.instances['edit-body-0-value'];
  var header_cols = editor.document.find('table > thead > tr > th');
  header_cols.getItem(0).setHtml('<i>Header text 1</i>');
  header_cols.getItem(1).setHtml('<i>Header text 2</i>');
})()
JS;
    $this->getSession()->evaluateScript($javascript);
    // Assert presence of classes in table related to "Zebra striping" option.
    $page->fillField('Page title', 'test');
    $this->click('#cke_edit-oe-teaser-0-value .cke_button__source');
    $page->find('css', 'textarea.cke_source')->setValue('test');
    $page->fillField('Content owner (value 1)', 'Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)');
    $page->fillField('Subject tags (value 1)', 'financing (http://data.europa.eu/uxp/1000)');
    $page->pressButton('Save');
    $web_assert->elementExists('css', 'article .ecl table.ecl-table.ecl-table--simple.ecl-table--zebra');
    // Assert absence of classes in table related to "Zebra striping" and
    // "Simple table" with disabled option.
    $this->drupalGet('node/1/edit');
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe('edit-body-0-value-instance-id');
    $this->getSession()->switchToIFrame('edit-body-0-value-instance-id');
    $page->find('css', 'table > tbody > tr > td')->rightClick();
    $this->getSession()->switchToIFrame();
    $this->getSession()->switchToIFrame($page->find('css', '.cke_menu_panel iframe')->getAttribute('id'));
    $this->clickLink('Table Properties');
    $this->getSession()->switchToIFrame();
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $dialog = $this->getSession()->getPage()->find('css', '.cke_editor_edit-body-0-value_dialog');
    $dialog->findField('Zebra striping')->click();
    $dialog->findField('Simple table')->click();
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');
    $page->pressButton('Save');
    $web_assert->elementNotExists('css', 'article .ecl table.ecl-table.ecl-table--zebra');
    $web_assert->elementExists('css', 'article .ecl table');

    // If the filter is not enabled in an editor,
    // the "Sort" plugin is not active.
    FilterFormat::load('full_html')
      ->setFilterConfig('filter_ecl_table', ['status' => FALSE])
      ->save();
    // Sortable field select box is not available as
    // "ECL Table" plugin is disabled.
    $this->drupalGet('node/1/edit');
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe('edit-body-0-value-instance-id');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertFalse($page->hasField('Sortable'));
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // If the filter is enabled in an editor,
    // the "Sort" plugin is active.
    FilterFormat::load('full_html')
      ->setFilterConfig('filter_ecl_table', ['status' => TRUE])
      ->save();
    $this->getSession()->reload();
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe('edit-body-0-value-instance-id');
    // The select is shown when right clicking a <th>.
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertFalse((bool) $this->getOpenedDialogElement()->findField('Sortable')->getAttribute('disabled'));
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // The select is disabled when right clicking a <td>.
    $this->openCellPropertiesDialog('table > tbody > tr > td');
    $this->assertTrue((bool) $this->getOpenedDialogElement()->findField('Sortable')->getAttribute('disabled'));
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // The select is shown when right clicking after selecting multiple <th>.
    $this->selectElementBySelector('table > thead > tr');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertFalse((bool) $this->getOpenedDialogElement()->findField('Sortable')->getAttribute('disabled'));
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // The select is disabled when right clicking
    // after selecting a mix of <th> and <td>.
    $this->selectElementBySelector('table');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertTrue((bool) $this->getOpenedDialogElement()->findField('Sortable')->getAttribute('disabled'));
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // Make sortable particular header column.
    $this->selectElementBySelector('table > thead > tr > th');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->getOpenedDialogElement()->selectFieldOption('Sortable', 'yes');
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');
    // Assert correct saving of header column sort state.
    $this->selectElementBySelector('table > thead > tr > th');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertEquals('yes', $this->getOpenedDialogElement()->findField('Sortable')->getValue());
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // Make not sortable particular header column.
    $this->selectElementBySelector('table > thead > tr > th');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->getOpenedDialogElement()->selectFieldOption('Sortable', 'no');
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');
    // Assert correct saving of header column sort state.
    $this->selectElementBySelector('table > thead > tr > th');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertEquals('no', $this->getOpenedDialogElement()->findField('Sortable')->getValue());
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // The select has no selection when the selection contains a mix of <th>
    // with and without the attribute.
    $this->selectElementBySelector('table > thead > tr > th');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->getOpenedDialogElement()->selectFieldOption('Sortable', 'yes');
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');
    $this->selectElementBySelector('table > thead > tr');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertTrue(empty($this->getOpenedDialogElement()->findField('Sortable')->getValue()));
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');
    // The attributes are untouched when if the dialog is submitted with the
    // "no selection" option at the step above.
    $this->selectElementBySelector('table > thead > tr > th');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertEquals('yes', $this->getOpenedDialogElement()->findField('Sortable')->getValue());
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // The attribute is added/removed on single/multiple <th> selections
    // based on the chosen value.
    $this->selectElementBySelector('table > thead > tr');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->getOpenedDialogElement()->selectFieldOption('Sortable', 'no');
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');
    $this->selectElementBySelector('table > thead > tr');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertEquals('no', $this->getOpenedDialogElement()->findField('Sortable')->getValue());
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    // Make sure that the selected column's sortability is correctly
    // reflected on frontend.
    $this->selectElementBySelector('table > thead > tr');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->getOpenedDialogElement()->selectFieldOption('Sortable', 'yes');
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');
    $this->selectElementBySelector('table > thead > tr');
    $this->openCellPropertiesDialog('table > thead > tr > th');
    $this->assertEquals('yes', $this->getOpenedDialogElement()->findField('Sortable')->getValue());
    $this->getOpenedDialogElement()->getParent()->getParent()->pressButton('OK');

    $page->pressButton('Save');
    $headers = $this->getSession()->getPage()->findAll('css', 'table thead tr th[data-ecl-table-sort-toggle]');
    $this->assertCount(2, $headers);
  }

  /**
   * Open Cell Property dialog based on provided selector for a table cell.
   *
   * @param string $right_click_selector
   *   Right click selector.
   */
  protected function openCellPropertiesDialog(string $right_click_selector): void {
    $web_assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->getSession()->switchToIFrame('edit-body-0-value-instance-id');
    $page->find('css', $right_click_selector)->rightClick();
    $this->getSession()->switchToIFrame();
    $web_assert->waitForElementVisible('xpath', "//div[contains(concat(' ', normalize-space(@class), ' '), ' cke_menu_panel ') and @hidden]/iframe");
    $this->getSession()->switchToIFrame($page->find('xpath', "//div[contains(concat(' ', normalize-space(@class), ' '), ' cke_menu_panel ') and not(@hidden)]/iframe")->getAttribute('id'));
    $this->clickLink('Cell');
    $this->getSession()->switchToIFrame();
    $web_assert->waitForElementVisible('xpath', "//div[contains(concat(' ', normalize-space(@class), ' '), ' cke_menu_panel ') and @hidden][last()]/iframe");
    $this->getSession()->switchToIFrame($page->find('xpath', "//div[contains(concat(' ', normalize-space(@class), ' '), ' cke_menu_panel ') and not(@hidden)][last()]/iframe")->getAttribute('id'));
    $this->clickLink('Cell Properties');
    $this->getSession()->switchToIFrame();
    $web_assert->waitForElementVisible('css', '#' . $this->getOpenedDialogElement()->getAttribute('id'));
  }

  /**
   * Get current dialog element related to the WYSIWYG.
   */
  protected function getOpenedDialogElement(): ?NodeElement {
    $javascript = <<<JS
(function(){
  return CKEDITOR.dialog.getCurrent().parts.contents.getId();
})()
JS;
    $dialogId = $this->getSession()->evaluateScript($javascript);
    return $this->getSession()->getPage()->findById($dialogId);
  }

  /**
   * Make selection inside the WYSIWYG content.
   *
   * @param string $selector
   *   Selector of element.
   * @param string $instance_id
   *   (optional) The CKEditor instance ID. Defaults to 'edit-body-0-value'.
   */
  protected function selectElementBySelector(string $selector, string $instance_id = 'edit-body-0-value'): void {
    $javascript = <<<JS
(function(){
  var editor = CKEDITOR.instances['{$instance_id}'];
  var selection = editor.getSelection();
  var element = editor.document.findOne('{$selector}');
  var range = editor.createRange();
  range.selectNodeContents(element);
  selection.removeAllRanges();
  selection.selectRanges([range]);
})()
JS;
    $this->getSession()->evaluateScript($javascript);
  }

  /**
   * Wait for a CKEditor instance to finish loading and initializing.
   *
   * @param string $instance_id
   *   The CKEditor instance ID.
   * @param int $timeout
   *   (optional) Timeout in milliseconds, defaults to 10000.
   */
  protected function waitOnCkeditorInstance($instance_id, $timeout = 10000) {
    $condition = <<<JS
      (function() {
        return (
          typeof CKEDITOR !== 'undefined'
          && typeof CKEDITOR.instances["$instance_id"] !== 'undefined'
          && CKEDITOR.instances["$instance_id"].instanceReady
        );
      }())
JS;

    $this->getSession()->wait($timeout, $condition);
  }

}
