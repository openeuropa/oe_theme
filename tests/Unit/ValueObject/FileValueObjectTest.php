<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\oe_theme\ValueObject\FileValueObject;
use Drupal\Tests\oe_theme\Unit\AbstractUnitTestBase;

/**
 * Test file value object.
 */
class FileValueObjectTest extends AbstractUnitTestBase {

  /**
   * Test constructing a file value object from an array.
   */
  public function testFromArray() {
    $data = [
      'size' => '123',
      'mime' => 'pdf',
      'name' => 'Test.pdf',
      'url' => 'http://example.com/test.pdf',
    ];

    /** @var \Drupal\oe_theme\ValueObject\FileValueObject $file */
    $file = FileValueObject::fromArray($data);

    $this->assertEquals('123', $file->size());
    $this->assertEquals('pdf', $file->mime());
    $this->assertEquals('http://example.com/test.pdf', $file->url());
    $this->assertEquals('Test.pdf', $file->name());
    $this->assertEquals('Test.pdf', $file->title());
    $this->assertEquals('pdf', $file->extension());
    $this->assertEquals('', $file->language_code());

    /** @var \Drupal\oe_theme\ValueObject\FileValueObject $file */
    $data['language_code'] = 'fr';
    $file = FileValueObject::fromArray($data);
    $this->assertEquals('fr', $file->language_code());
  }

}
