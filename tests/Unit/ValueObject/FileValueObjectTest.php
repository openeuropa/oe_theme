<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\oe_theme\ValueObject\Exception\ValueObjectFactoryException;
use Drupal\oe_theme\ValueObject\FileValueObject;
use Drupal\Tests\UnitTestCase;
use Drupal\file\Entity\File;

/**
 * Test file value object.
 */
class FileValueObjectTest extends UnitTestCase {

  /**
   * Test constructing a FileType object from an array.
   */
  public function testFromArrayOrAny() {
    $data = [
      'size' => '123',
      'mime' => 'pdf',
      'name' => 'Test.pdf',
      'url' => 'http://example.com/test.pdf',
    ];

    // Test that both factories yield the same result.
    foreach (['fromArray', 'fromAny'] as $factory) {
      $file = FileValueObject::$factory($data);

      $this->assertEquals('123', $file->getSize());
      $this->assertEquals('pdf', $file->getMime());
      $this->assertEquals('http://example.com/test.pdf', $file->getUrl());
      $this->assertEquals('Test.pdf', $file->getName());
    }
  }

  /**
   * Test constructing a FileType object from a File entity object.
   */
  public function testFromFileEntityOrAny() {
    $file = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getFileUri',
        'getMimeType',
        'getSize',
        'getFilename',
      ])->getMock();

    $file->expects($this->exactly(2))
      ->method('getFileUri')
      ->willReturn('http://example.com/test.pdf');
    $file->expects($this->exactly(2))
      ->method('getMimeType')
      ->willReturn('pdf');
    $file->expects($this->exactly(2))
      ->method('getSize')
      ->willReturn('123');
    $file->expects($this->exactly(2))
      ->method('getFilename')
      ->willReturn('Test.pdf');

    // Test that both factories yield the same result.
    foreach (['fromFileEntity', 'fromAny'] as $factory) {
      $data = FileValueObject::$factory($file);

      $this->assertEquals('123', $data->getSize());
      $this->assertEquals('pdf', $data->getMime());
      $this->assertEquals('http://example.com/test.pdf', $data->getUrl());
      $this->assertEquals('Test.pdf', $data->getName());
    }
  }

  /**
   * Test constructing a FileType object from an array.
   */
  public function testFactoryException() {
    $this->expectException(ValueObjectFactoryException::class);
    $this->expectExceptionMessage('Could not create instance of Drupal\oe_theme\ValueObject\FileValueObject. Initial value not supported.');
    FileValueObject::fromAny(new \stdClass());
  }

}
