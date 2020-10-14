<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the media thumbnail url field formatter.
 */
class MediaThumbnailUrlFormatterTest extends MediaThumbnailUrlFormatterTestBase {

  /**
   * Test media thumbnail url formatter.
   */
  public function testFormatter() {
    $media = $this->createMediaImage(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg');

    // Create an entity_reference field to test the widget.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_test',
      'type' => 'entity_reference',
      'entity_type' => 'entity_test',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'settings' => [
        'target_type' => 'media',
      ],
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'entity_test',
    ]);
    $field->save();

    $entity = EntityTest::create([
      'field_test' => [
        'target_id' => $media->id(),
      ],
    ]);
    $entity->save();

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('entity_test');

    // Test formatter without an image style.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_media_thumbnail_url',
      'label' => 'hidden',
    ]);
    $this->assertRendering($this->renderRoot($build), [
      'contains' => [
        // Because the media has no thumbnail configured we use the generic
        // no thumbnail file by default.
        'files/media-icons/generic/no-thumbnail.png',
      ],
    ]);

    // Test formatter with the medium image style.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_media_thumbnail_url',
      'label' => 'hidden',
      'settings' => [
        'image_style' => 'medium',
      ],
    ]);
    $this->assertRendering($this->renderRoot($build), [
      'contains' => [
        // Because the media has no thumbnail configured we use the generic
        // no thumbnail file by default.
        'files/styles/medium/public/media-icons/generic/no-thumbnail.png',
      ],
    ]);

    // Test formatter with the large image style.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_media_thumbnail_url',
      'label' => 'hidden',
      'settings' => [
        'image_style' => 'large',
      ],
    ]);
    $this->assertRendering($this->renderRoot($build), [
      'contains' => [
        // Because the media has no thumbnail configured we use the generic
        // no thumbnail file by default.
        'files/styles/large/public/media-icons/generic/no-thumbnail.png',
      ],
    ]);
  }

}
