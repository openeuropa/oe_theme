<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\Tests\oe_theme\Unit\AbstractUnitTestBase;
use Drupal\oe_theme\ValueObject\FileValueObject;

/**
 * Test file value object.
 *
 * @group batch2
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

    $this->assertEquals('123', $file->getSize());
    $this->assertEquals('pdf', $file->getMime());
    $this->assertEquals('http://example.com/test.pdf', $file->getUrl());
    $this->assertEquals('Test.pdf', $file->getName());
    $this->assertEquals('Test.pdf', $file->getTitle());
    $this->assertEquals('pdf', $file->getExtension());
    $this->assertEquals('', $file->getLanguageCode());

    /** @var \Drupal\oe_theme\ValueObject\FileValueObject $file */
    $data['language_code'] = 'fr';
    $file = FileValueObject::fromArray($data);
    $this->assertEquals('fr', $file->getLanguageCode());

    // Assert that urls are not escaped when returned by the object.
    $data['url'] = 'http://example.com/test.pdf?param1=a&param2=b';
    $file = FileValueObject::fromArray($data);
    $this->assertEquals('http://example.com/test.pdf?param1=a&param2=b', $file->getUrl());
  }

}
