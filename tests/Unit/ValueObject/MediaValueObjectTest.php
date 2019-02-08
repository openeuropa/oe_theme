<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\oe_theme\ValueObject\MediaValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test file value object.
 */
class MediaValueObjectTest extends UnitTestCase {

  /**
   * Test constructing a file value object from an array.
   */
  public function testFromArray() {
    $data = [
      'src' => 'http://example.com/test.pdf',
      'name' => 'Test.pdf',
    ];

    /** @var \Drupal\oe_theme\ValueObject\MediaValueObject $media */
    $object = MediaValueObject::fromArray($data);

    $this->assertEquals($data['src'], $object->getSource());
    $this->assertEquals($data['name'], $object->getName());
  }

}
