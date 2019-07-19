<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the timeline field type definition.
 */
class TimelineTest extends AbstractKernelTestBase {

  use RdfDatabaseConnectionTrait;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The node view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewBuilder;

  /**
   * A field storage to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The field used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * The display options to use in the formatter.
   *
   * @var array
   */
  protected $displayOptions = [
    'type' => 'timeline_formatter',
    'label' => 'hidden',
    'settings' => [
      'limit' => 2,
      'show_more' => 'Button label',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'link',
    'text',
    'node',
    'filter',
    'oe_content',
    'oe_content_timeline_field',
    'rdf_entity',
    'rdf_skos',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();
    $this->installEntitySchema('node');
    $this->installConfig([
      'field',
      'node',
      'filter',
      'rdf_entity',
    ]);
    $this->installSchema('user', 'users_data');

    $this->installEntitySchema('rdf_entity');
    $this->installEntitySchema('skos_concept');
    $this->installEntitySchema('skos_concept_scheme');

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);

    // Create content type.
    $type = NodeType::create(['name' => 'Test content type', 'type' => 'test_ct']);
    $type->save();

    FilterFormat::create([
      'format' => 'my_text_format',
      'name' => 'My text format',
      'filters' => [
        'filter_autop' => [
          'module' => 'filter',
          'status' => TRUE,
        ],
      ],
    ])->save();

    // Add text field to entity, to sort by.
    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_timeline',
      'entity_type' => 'node',
      'type' => 'timeline_field',
      'cardinality' => -1,
      'entity_types' => ['node'],
    ]);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'label' => 'Timeline field',
      'field_name' => 'field_timeline',
      'entity_type' => 'node',
      'bundle' => 'test_ct',
      'settings' => [],
      'required' => FALSE,
    ]);
    $this->field->save();

    EntityViewDisplay::create([
      'targetEntityType' => $this->field->getTargetEntityTypeId(),
      'bundle' => $this->field->getTargetBundle(),
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent($this->fieldStorage->getName(), $this->displayOptions)
      ->save();
  }

  /**
   * Test the timeline field rendering with formatter and ecl template.
   */
  public function testTimelineRender(): void {
    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'field_timeline' => [
        [
          'label' => '16/07/2019',
          'title' => 'Item 1',
          'body' => 'Item 1 body',
          'format' => 'my_text_format',
        ],
        [
          'label' => '16/07/2019',
          'title' => 'Item 2',
          'body' => 'Item 2 body',
          'format' => 'my_text_format',
        ],
        [
          'label' => '16/07/2019',
          'title' => 'Item 3',
          'body' => 'Item 3 body',
          'format' => 'my_text_format',
        ],
      ],
    ];

    // Create node.
    $node = Node::create($values);
    $node->save();

    // Verify the timeline is correctly rendered by ECL.
    $display = EntityViewDisplay::collectRenderDisplay($node, 'default');
    $build = $display->build($node);
    $output = $this->renderRoot($build);
    $crawler = new Crawler($output);

    $timeline_item = $crawler->filter('.ecl-timeline__item');
    $this->assertCount(3, $timeline_item);

    $timeline_title = $crawler->filter('.ecl-timeline__title');
    $this->assertCount(3, $timeline_title);

    $timeline_body = $crawler->filter('.ecl-timeline__body');
    $this->assertCount(3, $timeline_body);

    $hidden_timeline_item = $crawler->filter('.ecl-timeline__item--over-limit');
    $this->assertCount(1, $hidden_timeline_item);

    $show_more_button = $crawler->filter('.ecl-timeline__button');
    $this->assertCount(1, $show_more_button);

    // Change the limit to show all item without show more button.
    $this->displayOptions['settings']['limit'] = 0;
    $display->setComponent('field_timeline', $this->displayOptions)->save();

    $build = $display->build($node);
    $output = $this->renderRoot($build);
    $this->verbose($output);
    $this->assertNotContains('.ecl-timeline__button', (string) $output);
    $this->assertNotContains('.ecl-timeline__item--over-limit', (string) $output);
  }

}
