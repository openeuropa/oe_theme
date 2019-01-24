<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Unit\Patterns;

use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Entity\Plugin\DataType\EntityReference;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\oe_theme\ValueObject\ImageMediaValueObject;
use Drupal\Tests\UnitTestCase;

/**
 * Test file value object.
 */
class ImageMediaValueObjectTest extends UnitTestCase {

  /**
   * Mock ImageItem object.
   *
   * @var \Drupal\image\Plugin\Field\FieldType\ImageItem|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $imageReferenceItem;

  /**
   * Mock EntityAdapter object.
   *
   * @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $imageFile;

  /**
   * Mock EntityReference object.
   *
   * @var \Drupal\Core\Entity\Plugin\DataType\EntityReference|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $imageEntity;

  /**
   * Mock FieldItemList object.
   *
   * @var \Drupal\Core\Field\FieldItemList|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $imageUri;

  /**
   * Mock StringData object.
   *
   * @var \Drupal\Core\TypedData\Plugin\DataType\StringData|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $imageAltText;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->imageUri = $this->prophesize(FieldItemList::class);
    $this->imageUri->getString()->willReturn('http://placehold.it/380x185');

    $this->imageFile = $this->prophesize(EntityAdapter::class);
    $this->imageFile->get('uri')->willReturn($this->imageUri->reveal());

    $this->imageEntity = $this->prophesize(EntityReference::class);
    $this->imageEntity->getTarget()->willReturn($this->imageFile->reveal());

    $this->imageReferenceItem = $this->prophesize(ImageItem::class);
    $this->imageReferenceItem->get('entity')->willReturn($this->imageEntity->reveal());

    $this->imageAltText = $this->prophesize(StringData::class);
    $this->imageAltText->getString()->willReturn('Alt text');

    $this->imageReferenceItem->get('alt')->willReturn($this->imageAltText->reveal());
  }

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
    $image = ImageMediaValueObject::fromArray($data);

    $this->assertEquals('http://placehold.it/380x185', $image->getSource());
    $this->assertEquals('Test image', $image->getName());
    $this->assertEquals('Alt text', $image->getAlt());
    $this->assertEquals(TRUE, $image->isResponsive());
  }

  /**
   * Test constructing a file value object from an array.
   */
  public function testFromImageField() {
    /** @var \Drupal\oe_theme\ValueObject\ImageMediaValueObject $image */
    $image = ImageMediaValueObject::fromImageField('Test image', $this->imageReferenceItem->reveal());

    $this->assertEquals('http://placehold.it/380x185', $image->getSource());
    $this->assertEquals('Test image', $image->getName());
    $this->assertEquals('Alt text', $image->getAlt());
    $this->assertEquals(TRUE, $image->isResponsive());
  }

}

/**
 * Temporary mock for file_create_url().
 */
if (!function_exists('Drupal\Tests\oe_theme\Unit\Patterns\file_create_url')) {

  /**
   * Mock for file_create_url().
   *
   * @param string $uri
   *   Uri to be processed.
   *
   * @return string
   *   Process url.
   */
  function file_create_url($uri): string {
    return $uri;
  }

}

/**
 * Mocking file_create_url().
 *
 * ImageMediaValueObject uses file_create_url()
 * which is available when using the Simpletest test runner, but not when
 * using the PHPUnit test runner; hence this hack.
 */
namespace Drupal\oe_theme\ValueObject;

if (!function_exists('Drupal\oe_theme\ValueObject\file_create_url')) {

  /**
   * Mock for file_create_url().
   *
   * @param string $uri
   *   Uri to be processed.
   *
   * @return string
   *   Processed url.
   */
  function file_create_url($uri): string {
    return \Drupal\Tests\oe_theme\Unit\Patterns\file_create_url($uri);
  }

}
