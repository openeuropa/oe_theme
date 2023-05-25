<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\media\Entity\Media;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Base class for formatters rendering media thumbnail URLs.
 *
 * @group batch2
 */
class MediaThumbnailUrlFormatterTestBase extends AbstractKernelTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_reference',
    'entity_test',
    'field',
    'file',
    'media',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');
    $this->installSchema('file', 'file_usage');

    $this->installConfig([
      'file',
      'field',
      'entity_reference',
      'media',
    ]);

    // Call the install hook of the Media module.
    module_load_include('install', 'media');
    media_install();
  }

  /**
   * Create a media of type "image".
   *
   * @param string $filepath
   *   Full path to image file.
   *
   * @return \Drupal\media\Entity\Media
   *   Media object.
   */
  protected function createMediaImage(string $filepath): Media {
    $media_type = $this->createMediaType('image');

    $file = \Drupal::service('file.repository')->writeData(file_get_contents($filepath), 'public://' . basename($filepath));
    $file->setPermanent();
    $file->save();

    /** @var \Drupal\media\Entity\Media $media */
    $media = Media::create([
      'bundle' => $media_type->id(),
      'name' => 'test image',
      'field_media_file' => [
        'target_id' => $file->id(),
      ],
    ]);
    $media->save();

    return $media;
  }

}
