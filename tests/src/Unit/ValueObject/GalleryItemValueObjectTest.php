<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\ValueObject;

use Drupal\oe_theme\ValueObject\GalleryItemValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test gallery item value object.
 *
 * @group batch1
 */
class GalleryItemValueObjectTest extends UnitTestCase {

  /**
   * Test constructing a gallery item value object from an array.
   */
  public function testFromArray(): void {
    $image_value_object = ImageValueObject::fromArray([
      'src' => 'http://placehold.it/380x185',
      'name' => 'Test thumbnail',
      'alt' => 'Alt text',
      'responsive' => TRUE,
    ]);
    $values = [
      'thumbnail' => $image_value_object,
      'source' => 'http://placehold.it/600x400',
      'type' => GalleryItemValueObject::TYPE_VIDEO,
      'caption' => 'Test video caption.',
      'meta' => 'Test video meta.',
    ];

    /** @var \Drupal\oe_theme\ValueObject\GalleryItemValueObject $item */
    $item = GalleryItemValueObject::fromArray($values);

    $this->assertEquals($values['thumbnail'], $item->getThumbnail());
    $this->assertEquals($values['source'], $item->getSource());
    $this->assertEquals($values['type'], $item->getType());
    $this->assertEquals($values['caption'], $item->getCaption());
    $this->assertEquals($values['meta'], $item->getMeta());

    $to_array = $item->getArray();
    $this->assertEquals($to_array['picture']['img'], $image_value_object->getArray());
    $this->assertEquals($to_array['embedded_video'], [
      'src' => 'http://placehold.it/600x400',
    ]);
    $this->assertEquals($to_array['icon'], 'video');
    $this->assertEquals($to_array['description'], 'Test video caption.');
    $this->assertEquals($to_array['meta'], 'Test video meta.');

    // Verify that the thumbnail can be also passed as array.
    $item = GalleryItemValueObject::fromArray([
      'thumbnail' => $values['thumbnail']->getArray(),
    ] + $values);

    $this->assertEquals($values['thumbnail'], $item->getThumbnail());
    $this->assertEquals($values['source'], $item->getSource());
    $this->assertEquals($values['type'], $item->getType());
    $this->assertEquals($values['caption'], $item->getCaption());
    $this->assertEquals($values['meta'], $item->getMeta());

    // Create an image-based Gallery item.
    $values = [
      'thumbnail' => $image_value_object,
      'source' => 'http://placehold.it/800x600',
      'type' => GalleryItemValueObject::TYPE_IMAGE,
      'caption' => 'Test image caption.',
      'meta' => 'Test image meta.',
    ];

    /** @var \Drupal\oe_theme\ValueObject\GalleryItemValueObject $item */
    $item = GalleryItemValueObject::fromArray($values);
    $this->assertEquals($values['type'], $item->getType());
    $to_array = $item->getArray();
    $this->assertEquals($to_array['icon'], 'image');
    $this->assertEquals($to_array['description'], 'Test image caption.');
    $this->assertEquals($to_array['meta'], 'Test image meta.');
  }

  /**
   * Tests that the thumbnail cache metadata is merged in the gallery item one.
   */
  public function testCacheMetadataBubbling(): void {
    $thumbnail = ImageValueObject::fromArray([
      'src' => 'http://placehold.it/380x185',
    ]);
    $thumbnail->addCacheTags(['test:1']);
    $thumbnail->addCacheContexts(['test_context']);
    $thumbnail->mergeCacheMaxAge(3600);

    $item = GalleryItemValueObject::fromArray([
      'thumbnail' => $thumbnail,
    ]);

    $this->assertEquals($thumbnail->getCacheTags(), $item->getCacheTags());
    $this->assertEquals($thumbnail->getCacheContexts(), $item->getCacheContexts());
    $this->assertEquals($thumbnail->getCacheMaxAge(), $item->getCacheMaxAge());
  }

}
