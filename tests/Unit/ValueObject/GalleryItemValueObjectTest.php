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
      'path' => "http://placehold.it/285x185",
      'alt' => "Example alt text",
      'description' => "Example image caption",
      'share_path' => '/share#example-image.jpg',
      'meta' => 'Copyright, Author, Licence for image 1',
      'icon' => [
        'name' => "image",
        'icon_type' => "general",
      ],
    ];

    /** @var \Drupal\oe_theme\ValueObject\GalleryItemValueObject $galleryItem */
    $galleryItem = GalleryItemValueObject::fromArray($item);

    $this->assertEquals($item['path'], $galleryItem->getPath());
    $this->assertEquals($item['alt'], $galleryItem->getAlt());
    $this->assertEquals($item['icon'], $galleryItem->getIcon());
  }

}
