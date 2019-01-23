<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\oe_theme\ValueObject\MediaValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test file value object.
 */
class FileValueObjectTest extends UnitTestCase {

  /**
   * Test constructing a file value object from an array.
   */
  public function testFromArray() {
    $data = [
      'source' => 'http://placehold.it/380x185',
      'name' => 'Test image',
      'alt' => 'Alt text',
      'responsive' => TRUE,
    ];

    /** @var \Drupal\oe_theme\ValueObject\ImageMediaValueObject $image */
    $image = MediaValueObject::fromArray($data);

    $this->assertEquals('http://placehold.it/380x185', $image->getSource());
    $this->assertEquals('Test image', $image->getName());
    $this->assertEquals('Alt text', $image->getAlt());
    $this->assertEquals(TRUE, $image->isResponsive());
  }

  /**
   * Test constructing a file value object from an array.
   */
  public function testFromImageField() {
    // TODO: Add test.
  }

}
