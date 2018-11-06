<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Unit\Patterns;

use Drupal\oe_theme_helper\Patterns\FileType;
use Drupal\Tests\UnitTestCase;
use Drupal\file\Entity\File;

/**
 * Test file pattern field type.
 */
class FileTypeTest extends UnitTestCase {

  /**
   * Test constructing a FileType object from an array.
   */
  public function testFromArray() {
    $data = FileType::fromArray([
      'size' => '123',
      'mime' => 'pdf',
      'name' => 'Test.pdf',
      'url' => 'http://example.com/test.pdf',
    ]);

    $this->assertEquals('123', $data->getSize());
    $this->assertEquals('pdf', $data->getMime());
    $this->assertEquals('http://example.com/test.pdf', $data->getUrl());
    $this->assertEquals('Test.pdf', $data->getName());
  }

  /**
   * Test constructing a FileType object from a File entity object.
   */
  public function testFromFileEntity() {
    $file = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getFileUri',
        'getMimeType',
        'getSize',
        'getFilename',
      ])->getMock();

    $file->expects($this->once())
      ->method('getFileUri')
      ->willReturn('http://example.com/test.pdf');
    $file->expects($this->once())
      ->method('getMimeType')
      ->willReturn('pdf');
    $file->expects($this->once())
      ->method('getSize')
      ->willReturn('123');
    $file->expects($this->once())
      ->method('getFilename')
      ->willReturn('Test.pdf');

    $data = FileType::fromFileEntity($file);

    $this->assertEquals('123', $data->getSize());
    $this->assertEquals('pdf', $data->getMime());
    $this->assertEquals('http://example.com/test.pdf', $data->getUrl());
    $this->assertEquals('Test.pdf', $data->getName());
  }

}
