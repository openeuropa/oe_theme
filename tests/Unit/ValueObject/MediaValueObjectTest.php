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
      'source' => 'http://example.com/test.pdf',
      'name' => 'Test.pdf',
    ];

    /** @var \Drupal\oe_theme\ValueObject\MediaValueObject $media */
    $media = MediaValueObject::fromArray($data);

    $this->assertEquals('http://example.com/test.pdf', $media->getSource());
    $this->assertEquals('Test.pdf', $media->getName());
  }

}
