<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme_helper\FunctionalJavascript;

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
    $this->container->get('theme_installer')->install(['oe_theme', 'seven']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->config('system.theme')->set('admin', 'seven')->save();
    $this->config('node.settings')->set('use_admin_theme', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

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
    $web_assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalLogin($this->webUser);
    $this->drupalGet('node/add/oe_page');
    $this->waitOnCkeditorInstance('edit-body-0-value');
    $this->pressEditorButton('table');
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $web_assert->elementContains('css', '.cke_editor_edit-body-0-value_dialog .cke_dialog_ui_checkbox', 'Zebra striping');

    FilterFormat::load('full_html')
      ->setFilterConfig('filter_ecl_table', ['status' => FALSE])
      ->save();
    $this->getSession()->reload();
    $this->waitOnCkeditorInstance('edit-body-0-value');
    $this->pressEditorButton('table');
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $web_assert->elementNotExists('css', '.cke_editor_edit-body-0-value_dialog .cke_dialog_ui_checkbox');

    FilterFormat::load('full_html')
      ->setFilterConfig('filter_ecl_table', ['status' => TRUE])
      ->save();
    $this->getSession()->reload();
    $this->waitOnCkeditorInstance('edit-body-0-value');
    $this->pressEditorButton('table');
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $this->click('.cke_editor_edit-body-0-value_dialog .cke_dialog_ui_checkbox');
    $page->pressButton('OK');

    $this->pressEditorButton('source');
    $this->assertStringContainsString('<table border="1" cellpadding="1" cellspacing="1" data-striped="true"', $page->find('css', 'textarea.cke_source')
      ->getValue());
    $this->pressEditorButton('source');

    $this->assignNameToCkeditorIframe('edit-body-0-value-instance-id');
    $this->getSession()->switchToIFrame('edit-body-0-value-instance-id');
    $page->find('css', 'table > tbody > tr > td')->rightClick();
    $this->getSession()->switchToIFrame();
    $this->getSession()->switchToIFrame($page->find('css', '.cke_menu_panel iframe')->getAttribute('id'));
    $this->clickLink('Table Properties');
    $this->getSession()->switchToIFrame();
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $this->assertTrue($page->findField('Zebra striping')->isChecked());
    $page->find('css', '.cke_editor_edit-body-0-value_dialog[style~="flex;"] .cke_dialog_ui_button_ok')->click();

    $page->fillField('Page title', 'test');
    $this->click('#cke_edit-oe-teaser-0-value .cke_button__source');
    $page->find('css', 'textarea.cke_source')->setValue('test');
    $page->fillField('Content owner (value 1)', 'Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)');
    $page->pressButton('Save');
    $web_assert->elementExists('css', 'article .ecl table.ecl-table.ecl-table--zebra');

    $this->drupalGet('node/1/edit');
    $this->assignNameToCkeditorIframe('edit-body-0-value-instance-id');
    $this->getSession()->switchToIFrame('edit-body-0-value-instance-id');
    $page->find('css', 'table > tbody > tr > td')->rightClick();
    $this->getSession()->switchToIFrame();
    $this->getSession()->switchToIFrame($page->find('css', '.cke_menu_panel iframe')->getAttribute('id'));
    $this->clickLink('Table Properties');
    $this->getSession()->switchToIFrame();
    $this->assertNotEmpty($web_assert->waitForElement('css', '.cke_editor_edit-body-0-value_dialog'));
    $this->click('.cke_editor_edit-body-0-value_dialog .cke_dialog_ui_checkbox');
    $page->find('css', '.cke_editor_edit-body-0-value_dialog[style~="flex;"] .cke_dialog_ui_button_ok')->click();
    $page->pressButton('Save');
    $web_assert->elementNotExists('css', 'article .ecl table.ecl-table.ecl-table--zebra');
    $web_assert->elementExists('css', 'article .ecl table');
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
