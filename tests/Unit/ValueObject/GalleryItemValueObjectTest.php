<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\oe_theme\ValueObject\GalleryItemValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test gallery item value object.
 */
class GalleryItemValueObjectTest extends UnitTestCase {

  /**
   * Test constructing a gallery item value object from an array.
   */
  public function testFromArray() {
    $item = [
      'icon' => 'camera',
      'caption' => 'Test caption.',
      'classes' => 'example-class',
      'thumbnail' => [
        'src' => 'http://placehold.it/380x185',
        'name' => 'Test thumbnail',
        'alt' => 'Alt text',
        'responsive' => TRUE,
      ],
    ];

    /** @var \Drupal\oe_theme\ValueObject\GalleryItemValueObject $galleryItem */
    $galleryItem = GalleryItemValueObject::fromArray($item);

    $this->assertEquals($item['icon'], $galleryItem->getIcon());
    $this->assertEquals($item['caption'], $galleryItem->getCaption());
    $this->assertEquals($item['classes'], $galleryItem->getClasses());
    $this->assertEquals($item['thumbnail'], $galleryItem->getThumbnail()->getArray());
  }

}
