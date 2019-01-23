<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\oe_theme\ValueObject\ImageMediaValueObject;
use Drupal\oe_theme\ValueObject\GalleryValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test file value object.
 */
class GalleryValueObjectTest extends UnitTestCase {

  /**
   * Test constructing a file value object from an array.
   */
  public function testFromArray() {
    $image_array = [
      'source' => 'http://placehold.it/380x185',
      'name' => 'Test image',
      'alt' => 'Alt text',
      'responsive' => TRUE,
    ];
    $image = ImageMediaValueObject::fromArray($image_array);
    $data = [
      'icon' => 'camera',
      'caption' => 'Test caption.',
      'classes' => 'example-class',
      'image' => $image,
    ];

    /** @var \Drupal\oe_theme\ValueObject\GalleryValueObject $galleryItem */
    $galleryItem = GalleryValueObject::fromArray($data);

    $this->assertEquals('camera', $galleryItem->getIcon());
    $this->assertEquals('Test caption.', $galleryItem->getCaption());
    $this->assertEquals('example-class', $galleryItem->getClasses());
    $this->assertEquals($image_array, $galleryItem->getImage()->toArray());

  }

}
