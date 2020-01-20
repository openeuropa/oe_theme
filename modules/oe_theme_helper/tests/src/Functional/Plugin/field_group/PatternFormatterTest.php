<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Functional\Plugin\field_group;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\field_group\Functional\FieldGroupTestTrait;

/**
 * Test pattern field group formatters.
 */
class PatternFormatterTest extends BrowserTestBase {

  use FieldGroupTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'text',
    'field_ui',
    'field_group',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Enable oe_theme and set it as default.
    $this->assertTrue($this->container->get('theme_installer')->install(['oe_theme']));
    $this->container->get('config.factory')
      ->getEditable('system.theme')
      ->set('default', 'oe_theme')
      ->save();

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    $this->container->get('plugin.manager.ui_patterns')->clearCachedDefinitions();

    // Create test user.
    $admin_user = $this->drupalCreateUser([
      'access content',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
      'bypass node access',
    ]);
    $this->drupalLogin($admin_user);

    // Create content type, with underscores.
    $this->drupalCreateContentType([
      'name' => 'Test',
      'type' => 'test',
    ]);

    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
    $display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('node.test.default');

    // Create test fields.
    $fields = [
      'field_test_1' => 'Field 1',
      'field_test_2' => 'Field 2',
    ];
    foreach ($fields as $field_name => $field_label) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'type' => 'text',
      ]);
      $field_storage->save();

      $instance = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => 'test',
        'label' => $field_label,
      ]);
      $instance->save();

      // Set the field visible on the display object.
      $display->setComponent($field_name, [
        'label' => 'above',
        'type' => 'text_default',
      ]);
    }

    // Save display + create node.
    $display->save();
  }

  /**
   * Test field list pattern formatter.
   */
  public function testFieldListPatternFormatter() {
    $assert_session = $this->assertSession();

    $data = [
      'weight' => '1',
      'children' => [
        0 => 'field_test_1',
        1 => 'field_test_2',
      ],
      'label' => 'Test label',
      'format_type' => 'oe_theme_helper_field_list_pattern',
      'format_settings' => [
        'label' => 'Test label',
        'variant' => 'default',
      ],
    ];
    $group = $this->createGroup('node', 'test', 'view', 'default', $data);

    $this->drupalCreateNode([
      'type' => 'test',
      'field_test_1' => [
        ['value' => 'Content test 1'],
      ],
      'field_test_2' => [
        ['value' => 'Content test 2'],
      ],
    ]);

    // Assert that fields are rendered using the field list default pattern.
    $this->drupalGet('node/1');

    $element_selector = 'dl.ecl-description-list.ecl-description-list--default';
    $assert_session->elementExists('css', $element_selector);
    $assert_session->elementTextContains('css', $element_selector . ' dt.ecl-description-list__term:nth-child(1)', 'Field 1');
    $assert_session->elementTextContains('css', $element_selector . ' dd.ecl-description-list__definition:nth-child(2)', 'Content test 1');
    $assert_session->elementTextContains('css', $element_selector . ' dt.ecl-description-list__term:nth-child(3)', 'Field 2');
    $assert_session->elementTextContains('css', $element_selector . ' dd.ecl-description-list__definition:nth-child(4)', 'Content test 2');

    // Set pattern variant to "horizontal".
    $group->format_settings['variant'] = 'horizontal';
    field_group_group_save($group);

    // Assert that fields are rendered using the field list horizontal pattern.
    $this->drupalGet('node/1');

    $element_selector = 'dl.ecl-description-list.ecl-description-list--horizontal';
    $assert_session->elementExists('css', $element_selector);
    $assert_session->elementTextContains('css', $element_selector . ' dt.ecl-description-list__term:nth-child(1)', 'Field 1');
    $assert_session->elementTextContains('css', $element_selector . ' dd.ecl-description-list__definition:nth-child(2)', 'Content test 1');
    $assert_session->elementTextContains('css', $element_selector . ' dt.ecl-description-list__term:nth-child(3)', 'Field 2');
    $assert_session->elementTextContains('css', $element_selector . ' dd.ecl-description-list__definition:nth-child(4)', 'Content test 2');
  }

}
