<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\UrlHelper;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the featured media formatter.
 */
class FeaturedMediaFormatterTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'node',
    'media',
    'file',
    'image',
    'oe_media',
    'oe_content_featured_media_field',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('media');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installConfig([
      'media',
      'image',
      'file',
      'system',
      'oe_media',
      'oe_content_featured_media_field',
    ]);

    // Create a content type.
    $type = NodeType::create(['name' => 'Test content type', 'type' => 'test_ct']);
    $type->save();

    FieldStorageConfig::create([
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'type' => 'oe_featured_media',
      'cardinality' => 1,
      'entity_types' => ['node'],
    ])->save();

    FieldConfig::create([
      'label' => 'Featured media field',
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'bundle' => 'test_ct',
      'settings' => [
        'handler' => 'default:media',
        'handler_settings' => [
          'target_bundles' => [
            'image' => 'image',
          ],
        ],
        'sort' => [
          'field' => '_none',
        ],
        'auto_create' => '0',
      ],
      'required' => FALSE,
    ])->save();
  }

  /**
   * Test the featured media formatter.
   */
  public function testFormatter(): void {
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create(['uri' => 'public://example.jpg']);
    $image->save();

    $media = \Drupal::service('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'image',
        'name' => 'Test image',
        'oe_media_image' => [
          [
            'target_id' => $image->id(),
            'alt' => 'default alt',
          ],
        ],
      ]);
    $media->save();

    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'featured_media_field' => [
        [
          'target_id' => $media->id(),
          'caption' => 'Image caption text',
        ],
      ],
    ];

    /** @var \Drupal\node\NodeInterface $node */
    $node = Node::create($values);
    $node->save();

    $node_storage = \Drupal::service('entity_type.manager')->getStorage('node');
    $node_storage->resetCache();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $node_storage->load($node->id());

    $view_builder = \Drupal::service('entity_type.manager')->getViewBuilder('node');

    $build = $view_builder->viewField($node->get('featured_media_field'), [
      'type' => 'oe_theme_helper_featured_media_formatter',
    ]);

    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        '.ecl-media-container .ecl-media-container__media[alt="default alt"][src="' . file_create_url('public://example.jpg') . '"]' => 1,
        '.ecl-media-container .ecl-media-container__caption' => 1,
      ],
      'equals' => [
        '.ecl-media-container__caption' => 'Image caption text',
      ],
    ]);

    $media = \Drupal::service('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'remote_video',
        'oe_media_oembed_video' => 'https://www.youtube.com/watch?v=OkPW9mK5Vw8',
        'status' => 1,
      ]);
    $media->save();

    $values = [
      'type' => 'test_ct',
      'title' => 'My video node',
      'featured_media_field' => [
        [
          'target_id' => $media->id(),
          'caption' => 'Video caption text',
        ],
      ],
    ];

    $node = Node::create($values);
    $node->save();

    $node_storage->resetCache();
    $node = $node_storage->load($node->id());

    $build = $view_builder->viewField($node->get('featured_media_field'), [
      'type' => 'oe_theme_helper_featured_media_formatter',
    ]);

    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        '.ecl-media-container .ecl-media-container__media' => 1,
        '.ecl-media-container iframe.media-oembed-content[src*="' . UrlHelper::encodePath('https://www.youtube.com/watch?v=OkPW9mK5Vw8') . '"]' => 1,
        '.ecl-media-container .ecl-media-container__caption' => 1,
      ],
      'equals' => [
        '.ecl-media-container__caption' => 'Video caption text',
      ],
    ]);
  }

}
