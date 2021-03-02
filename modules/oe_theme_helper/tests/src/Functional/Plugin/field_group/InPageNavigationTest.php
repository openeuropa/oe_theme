<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Functional\Plugin\field_group;

use Behat\Mink\Element\NodeElement;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\field_group\Functional\FieldGroupTestTrait;

/**
 * Test In-page navigation field group.
 */
class InPageNavigationTest extends BrowserTestBase {

  use FieldGroupTestTrait;

  /**
   * The node type id.
   *
   * @var string
   */
  protected $type;

  /**
   * A node to use for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'extra_field_test',
    'field_test',
    'field_group',
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    \Drupal::service('theme_installer')->install(['oe_theme']);
    \Drupal::configFactory()->getEditable('system.theme')->set('default', 'oe_theme')->save();

    // Create content type.
    $this->type = 'first_node_type';
    $this->drupalCreateContentType(['name' => 'Test type', 'type' => $this->type]);

    // Create view display instance.
    $display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('node.' . $this->type . '.default');

    // Create a node.
    $node_values = ['type' => $this->type];

    // Create 4 test fields.
    for ($i = 0; $i < 4; $i++) {
      $field_name = "field_test_$i";
      $this->createTestField($field_name);

      // Assign a test value for the field.
      $node_values[$field_name][0]['value'] = ($i + 1) * 100;

      // Set the field visible on the display object.
      $display_options = [
        'label' => 'above',
        'type' => 'field_test_default',
        'weight' => $i,
        'settings' => [
          'test_formatter_setting' => $this->randomMachineName(),
        ],
      ];
      $display->setComponent($field_name, $display_options);
    }

    $display->save();
    $this->node = $this->drupalCreateNode($node_values);
  }

  /**
   * Tests the in-page navigation field group formatters.
   *
   * Structure of the content:
   * Field group "In-page navigation group"
   * - Field group "In-page navigation item"
   * -- Field 0
   * -- Field 1
   * - Field group "In-page navigation item"
   * -- Single text extra field
   * -- Multiple items extra field
   * - Field group "In-page navigation item"
   * -- Field group "Html elements"
   * --- Test field 2
   * --- Test field 3
   * - Field group "In-page navigation item"
   * -- Formatted extra field without content.
   */
  public function testOutput(): void {
    // Create "Html element" field group.
    $children_group_html_element = [
      'field_test_2',
      'field_test_3',
    ];
    $group_html_element = $this->createFieldGroup('html_element', 'html_element', $children_group_html_element, 10, ['show_label' => TRUE]);

    // Create "In-page navigation items" field groups.
    $children_group_inpage_nav_items = [
      [
        'field_test_0',
        'field_test_1',
      ],
      [
        'extra_field_single_text_test',
        'extra_field_multiple_text_test',
      ],
      [
        $group_html_element->group_name,
      ],
      // It is empty extra field, so field group shouldn't be rendered.
      [
        'extra_field_empty_formatted_test',
      ],
    ];
    $groups_inpage_nav_item = [];
    $children_group_inpage_nav = [];
    foreach ($children_group_inpage_nav_items as $index => $item) {
      $group = $this->createFieldGroup("inpage_nav_item_$index", 'oe_theme_helper_in_page_navigation_item', $item, $index);
      $groups_inpage_nav_item[] = $group;
      $children_group_inpage_nav[] = $group->group_name;
    }

    // Create In-page navigation group.
    $this->createFieldGroup('main', 'oe_theme_helper_in_page_navigation', $children_group_inpage_nav);
    $this->drupalGet('node/' . $this->node->id());

    // Assert navigation part.
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation', $wrapper);
    $navigation_title = $navigation->find('css', '.ecl-inpage-navigation__title');
    $this->assertEquals('Field group main', $navigation_title->getText());
    $navigation_list = $this->assertSession()->elementExists('css', '.ecl-inpage-navigation__list', $wrapper);
    $navigation_list_items = $navigation_list->findAll('css', '.ecl-inpage-navigation__item');
    $this->assertCount(3, $navigation_list_items);
    foreach ($navigation_list_items as $index => $item) {
      $navigation_list_item_link = $item->find('css', 'a.ecl-inpage-navigation__link');
      $this->assertEquals("Field group inpage_nav_item_$index", $navigation_list_item_link->getText());
      $this->assertEquals("#field-group-inpage-nav-item-$index", $navigation_list_item_link->getAttribute('href'));
    }

    // Assert content part.
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);
    // Assert headers of field groups.
    foreach ($content_items as $index => $item) {
      $this->assertContentHeader($item, $index);
    }

    // Assert first field group.
    $content_first_group = $content_items[0]->getText();
    $this->assertContains('Field label field_test_0', $content_first_group);
    $this->assertContains('100', $content_first_group);
    $this->assertContains('Field label field_test_1', $content_first_group);
    $this->assertContains('200', $content_first_group);

    // Assert second field group.
    $content_second_group = $content_items[1]->getText();
    $this->assertContains('Single text', $content_second_group);
    $this->assertContains('Output from SingleTextFieldTest', $content_second_group);
    $this->assertContains('Aap', $content_second_group);
    $this->assertContains('Noot', $content_second_group);

    // Assert third field group.
    $content_third_group = $content_items[2]->getText();
    $this->assertContains('Field group html_element', $content_third_group);
    $this->assertContains('Field label field_test_2', $content_third_group);
    $this->assertContains('300', $content_third_group);
    $this->assertContains('Field label field_test_3', $content_third_group);
    $this->assertContains('400', $content_third_group);

    // Assert fourth field group - it mustn't exist.
    $this->assertSession()->elementTextNotContains('css', 'body', 'Field group inpage_nav_item_3');
  }

  /**
   * Creates field group.
   *
   * @param string $name
   *   Name of the group.
   * @param string $format_type
   *   Group type.
   * @param array $children
   *   Children elements.
   * @param int $weight
   *   Weight of the element.
   * @param array $format_settings
   *   Group display settings.
   *
   * @return object
   *   An object that represents the field group.
   */
  protected function createFieldGroup(string $name, string $format_type, array $children, int $weight = 0, array $format_settings = []): object {
    $data = [
      'label' => "Field group $name",
      'weight' => $weight,
      'group_name' => $format_type . '_' . $name,
      'children' => $children,
      'format_type' => $format_type,
      'format_settings' => $format_settings,
    ];
    return $this->createGroup('node', $this->type, 'view', 'default', $data);
  }

  /**
   * Creates test field.
   *
   * @param string $field_name
   *   Field name.
   */
  protected function createTestField(string $field_name): void {
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'test_field',
    ]);
    $field_storage->save();

    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->type,
      'label' => "Field label $field_name",
    ]);
    $instance->save();
  }

  /**
   * Asserts field group header.
   *
   * @param \Behat\Mink\Element\NodeElement $content_item
   *   Field group content.
   * @param int $number
   *   Number of the element.
   */
  protected function assertContentHeader(NodeElement $content_item, int $number): void {
    $header = $content_item->find('css', 'h2.ecl-u-type-heading-2');
    $this->assertEquals("Field group inpage_nav_item_$number", $header->getText());
    $this->assertEquals("field-group-inpage-nav-item-$number", $header->getAttribute('id'));
  }

}
