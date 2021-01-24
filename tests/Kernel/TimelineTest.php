<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the timeline field rendering.
 */
class TimelineTest extends AbstractKernelTestBase {

  use SparqlConnectionTrait;

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
  protected static $modules = [
    'field',
    'link',
    'text',
    'node',
    'filter',
    'oe_content',
    'oe_content_timeline_field',
    'sparql_entity_storage',
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
    ]);
    $this->installSchema('user', 'users_data');

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

    // Add a timeline field.
    $fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_timeline',
      'entity_type' => 'node',
      'type' => 'timeline_field',
      'cardinality' => -1,
      'entity_types' => ['node'],
    ]);
    $fieldStorage->save();

    $field = FieldConfig::create([
      'label' => 'Timeline field',
      'field_name' => 'field_timeline',
      'entity_type' => 'node',
      'bundle' => 'test_ct',
      'settings' => [],
      'required' => FALSE,
    ]);
    $field->save();

    EntityViewDisplay::create([
      'targetEntityType' => $field->getTargetEntityTypeId(),
      'bundle' => $field->getTargetBundle(),
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent($fieldStorage->getName(), $this->displayOptions)
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

    // Test that the timeline is rendered using the formatter configuration.
    $display = EntityViewDisplay::collectRenderDisplay($node, 'default');
    $build = $display->build($node);
    $output = $this->renderRoot($build);
    $crawler = new Crawler($output);

    // Assert the timeline items are the number of entries plus one for the
    // "See more" button.
    $timeline_item = $crawler->filter('.ecl-timeline2__item');
    $this->assertCount(4, $timeline_item);

    $timeline_body = $crawler->filter('.ecl-timeline2__label');
    $this->assertCount(3, $timeline_body);

    $timeline_title = $crawler->filter('.ecl-timeline2__title');
    $this->assertCount(3, $timeline_title);

    $timeline_body = $crawler->filter('.ecl-timeline2__content');
    $this->assertCount(3, $timeline_body);

    $hidden_timeline_item = $crawler->filter('.ecl-timeline2__item--collapsed');
    $this->assertCount(1, $hidden_timeline_item);

    $show_more_button = $crawler->filter('.ecl-timeline2__item--toggle');
    $this->assertCount(1, $show_more_button);

    // Change the limit to show all items without the "show more" button.
    $this->displayOptions['settings']['limit'] = 0;
    $display->setComponent('field_timeline', $this->displayOptions)->save();

    $build = $display->build($node);
    $output = $this->renderRoot($build);
    $this->assertNotContains('.ecl-timeline2__item--toggle', (string) $output);
    $this->assertNotContains('.ecl-timeline2__item--collapsed', (string) $output);
  }

}
