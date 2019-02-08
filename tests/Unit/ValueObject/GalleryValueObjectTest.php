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
    $image_data = [
      'src' => 'http://placehold.it/380x185',
      'name' => 'Test image',
      'alt' => 'Alt text',
      'responsive' => TRUE,
    ];

    /** @var \Drupal\oe_theme\ValueObject\ImageMediaValueObject $image */
    $image = ImageMediaValueObject::fromArray($image_data);

    $data = [
      'icon' => 'camera',
      'caption' => 'Test caption.',
      'classes' => 'example-class',
      'image' => $image,
    ];

    /** @var \Drupal\oe_theme\ValueObject\GalleryValueObject $galleryItem */
    $galleryItem = GalleryValueObject::fromArray($data);

    $this->assertEquals($data['icon'], $galleryItem->getIcon());
    $this->assertEquals($data['caption'], $galleryItem->getCaption());
    $this->assertEquals($data['classes'], $galleryItem->getClasses());
    $this->assertEquals($image_data, $galleryItem->getImage()->getArray());
  }

}
