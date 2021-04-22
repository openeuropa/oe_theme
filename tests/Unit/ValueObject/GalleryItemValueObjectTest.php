<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\ValueObject;

use Drupal\oe_theme\ValueObject\GalleryItemValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test gallery item value object.
 */
class GalleryItemValueObjectTest extends UnitTestCase {

  /**
   * Test constructing a gallery item value object from an array.
   */
  public function testFromArray(): void {
    $values = [
      'thumbnail' => ImageValueObject::fromArray([
        'src' => 'http://placehold.it/380x185',
        'name' => 'Test thumbnail',
        'alt' => 'Alt text',
        'responsive' => TRUE,
      ]),
      'source' => 'http://placehold.it/600x400',
      'type' => GalleryItemValueObject::TYPE_VIDEO,
      'caption' => 'Test caption.',
      'meta' => 'Test meta.',
    ];

    /** @var \Drupal\oe_theme\ValueObject\GalleryItemValueObject $item */
    $item = GalleryItemValueObject::fromArray($values);

    $this->assertEquals($values['thumbnail'], $item->getThumbnail());
    $this->assertEquals($values['source'], $item->getSource());
    $this->assertEquals($values['type'], $item->getType());
    $this->assertEquals($values['caption'], $item->getCaption());
    $this->assertEquals($values['meta'], $item->getMeta());

    // Verify that the thumbnail can be also passed as array.
    $item = GalleryItemValueObject::fromArray([
      'thumbnail' => $values['thumbnail']->getArray(),
    ] + $values);

    $this->assertEquals($values['thumbnail'], $item->getThumbnail());
    $this->assertEquals($values['source'], $item->getSource());
    $this->assertEquals($values['type'], $item->getType());
    $this->assertEquals($values['caption'], $item->getCaption());
    $this->assertEquals($values['meta'], $item->getMeta());
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
