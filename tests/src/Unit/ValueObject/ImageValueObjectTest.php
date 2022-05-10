<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\ValueObject;

use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test image value object.
 *
 * @group batch1
 */
class ImageValueObjectTest extends UnitTestCase {

  /**
   * Test constructing an image value object from an array.
   */
  public function testFromArray() {
    $data = [
      'src' => 'http://placehold.it/380x185',
      'name' => 'Test image',
      'alt' => 'Alt text',
      'responsive' => TRUE,
    ];

    /** @var \Drupal\oe_theme\ValueObject\ImageValueObject $object */
    $object = ImageValueObject::fromArray($data);

    $this->assertEquals($data['src'], $object->getSource());
    $this->assertEquals($data['name'], $object->getName());
    $this->assertEquals($data['alt'], $object->getAlt());
    $this->assertEquals($data['responsive'], $object->isResponsive());
  }

}
