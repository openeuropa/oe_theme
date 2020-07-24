<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Functional\Plugin\field_group;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\field_group\Functional\FieldGroupTestTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test the in page navigation field group formatter.
 */
class FieldInPageNavigationFormatterTest extends BrowserTestBase {

  use FieldGroupTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'text',
    'field_ui',
    'field_group',
    'oe_theme_helper',
  ];

  /**
   * A node to use for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

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

    // Create content type.
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
      'field_test_1' => [
        'content' => 'Field 1 content',
        'label' => 'Field 1 label',
        'visibility' => 'above',
        'type' => 'string',
        'weight' => 11,
      ],
      'field_test_2' => [
        'content' => 'Field 2 content',
        'label' => 'Field 2 label',
        'visibility' => 'inline',
        'type' => 'string',
        'weight' => 21,
      ],
      'field_test_3' => [
        'content' => '',
        'label' => 'Field 3 label',
        'visibility' => 'inline',
        'type' => 'string',
        'weight' => 31,
      ],
      'field_test_4' => [
        'content' => 'Field 4 content',
        'label' => 'Field 4 label',
        'visibility' => 'above',
        'type' => 'string',
        'weight' => 41,
      ],
      'field_test_5' => [
        'content' => 'Field 5 content',
        'label' => 'Field 5 label',
        'visibility' => 'above',
        'type' => 'string',
        'weight' => 61,
      ],
      'field_test_6' => [
        'content' => 'Field 6 content',
        'label' => 'Field 6 label',
        'visibility' => 'hidden',
        'type' => 'string',
        'weight' => 62,
      ],
    ];
    $node_values = ['type' => "test"];

    foreach ($fields as $field_name => $field) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'cardinality' => 1,
        'type' => 'text',
      ]);
      $field_storage->save();

      $instance = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => 'test',
        'entity_type' => 'node',
        'settings' => [],
        'label' => $field['label'],
        'required' => FALSE,
      ]);
      $instance->save();

      // Set the field visible on the display object.
      $display->setComponent($field_name, [
        'label' => $field['visibility'],
        'type' => 'text_default',
        'weight' => $field['weight'],
      ]);
      // Create values for the testing node.
      if ($field['content'] !== '') {
        $node_values[$field_name][0]['value'] = $field['content'];
      }
    }
    // Save display + create node.
    $display->save();
    $this->node = $this->drupalCreateNode($node_values);
  }

  /**
   * Test the in page navigation field group formatter.
   */
  public function testFieldInPageNavigationFormmaterTest() {
    // Create display groups.
    $data = [
      'weight' => 10,
      'label' => 'Group 1',
      'children' => [
        0 => 'field_test_1',
      ],
      'format_type' => 'details',
      'format_settings' => [
        'open' => TRUE,
      ],
    ];
    $group_1 = $this->createGroup('node', 'test', 'view', 'default', $data);
    $data = [
      'weight' => 20,
      'label' => 'Group 2',
      'children' => [
        0 => 'field_test_2',
      ],
      'format_type' => 'details',
      'format_settings' => [
        'id' => 'group_2_id',
        'open' => TRUE,
      ],
    ];
    $group_2 = $this->createGroup('node', 'test', 'view', 'default', $data);
    $data = [
      'weight' => 30,
      'label' => 'Group 3',
      'children' => [
        0 => 'field_test_3',
      ],
      'format_type' => 'html_element',
      'format_settings' => [
        'show_label' => TRUE,
      ],
    ];
    $group_3 = $this->createGroup('node', 'test', 'view', 'default', $data);
    $data = [
      'weight' => 40,
      'label' => 'Group 4',
      'children' => [
        0 => 'field_test_4',
      ],
      'format_type' => 'details',
      'format_settings' => [
        'open' => TRUE,
      ],
    ];
    $group_4 = $this->createGroup('node', 'test', 'view', 'default', $data);
    $data = [
      'weight' => 50,
      'label' => 'Group 5',
      'format_type' => 'details',
      'format_settings' => [
        'open' => TRUE,
      ],
    ];
    $group_5 = $this->createGroup('node', 'test', 'view', 'default', $data);
    $data = [
      'weight' => 60,
      'label' => 'Group 6',
      'format_type' => 'html_element',
      'format_settings' => [
        'show_label' => FALSE,
      ],
    ];
    $group_6 = $this->createGroup('node', 'test', 'view', 'default', $data);
    $data = [
      'label' => 'In-page navigation group',
      'weight' => 0,
      'group_name' => 'group_inpage_nav',
      'children' => [
        $group_1->group_name,
        $group_2->group_name,
        $group_3->group_name,
        $group_4->group_name,
        $group_5->group_name,
        $group_6->group_name,
        'field_test_5',
        'field_test_6',
      ],
      'format_type' => 'oe_theme_helper_in_page_navigation',
    ];
    $inline = $this->createGroup('node', 'test', 'view', 'default', $data);
    $html = $this->drupalGet('node/' . $this->node->id());
    $crawler = new Crawler($html);
    // The content is break into two elements, for navigation and content.
    $navigation_selector = 'article .ecl-container .ecl-col-lg-3 .ecl-inpage-navigation';
    $content_selector = 'article .ecl-container .ecl-col-lg-9';
    $this->assertSession()->elementExists('css', $navigation_selector);
    $this->assertSession()->elementExists('css', $content_selector);
    // The navigation links match the content anchors.
    $anchor_links = [
      [
        'id' => 'inline-nav-1',
        'label' => 'Group 1',
        'selector' => 'summary',
      ], [
        // If a group has an id configured in the group configuration,
        // use that as anchor.
        'id' => 'group_2_id',
        'label' => 'Group 2',
        'selector' => 'summary',
      ], [
        'id' => 'inline-nav-2',
        'label' => 'Group 4',
        'selector' => 'summary',
      ], [
        'id' => 'inline-nav-3',
        'label' => 'Field 5 label',
        'selector' => 'div',
      ],
    ];
    foreach ($anchor_links as $key => $value) {
      $element = $crawler->filter($navigation_selector . " #ecl-inpage-navigation-list  [href='#" . $value['id'] . "']");
      // Link label.
      $this->assertEquals($value['label'], trim($element->text()));
      // Content label.
      $this->assertSession()->elementTextContains('css', $content_selector . " #" . $value['id'] . " > " . $value['selector'], $value['label']);
    }
    // If a group has no fields, don't include it in the navigation.
    $this->assertSession()->pageTextNotContains('Group 5');
    // If a group has no visible label, don't include it in the navigation.
    $this->assertSession()->pageTextNotContains('Group 6');
    // If a field has no visible label, don't include it in the navigation.
    $this->assertSession()->pageTextNotContains('Field 6 label');
    // If a group has all empty fields, don't show it in the navigation.
    $this->assertSession()->pageTextNotContains('Group 3');
  }

}
