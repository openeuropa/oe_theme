<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\Tests\token\Kernel\KernelTestBase;

/**
 * Test image value object with image field type.
 */
class ImageTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'image',
    'file',
    'entity_test',
    'field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('file');
    $this->installEntitySchema('entity_test');

    $this->installSchema('file', ['file_usage']);

    $this->installConfig(['field', 'system']);

    // Create a field with settings to validate.
    $field_storage = FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_image',
      'type' => 'image',
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'entity_test',
    ])->save();

    // Copy file in public files to use it for styling.
    \Drupal::service('file_system')->copy(__DIR__ . '/../../fixtures/example_1.jpeg', 'public://example_1.jpg');
  }

  /**
   * Test the image value object has the correct style applied.
   */
  public function testFromStyledImageItem() {
    // Create image file.
    $image = File::create([
      'uri' => 'public://example_1.jpg',
    ]);
    $image->save();

    // Create a test style.
    /** @var \Drupal\image\ImageStyleInterface $style */
    $style = ImageStyle::create(['name' => 'main_style']);
    $style->save();

    // Create an image item.
    $alt = $this->randomMachineName();
    $title = $this->randomMachineName();
    $entity = EntityTest::create([
      'name' => $this->randomString(),
      'field_image' => [
        'target_id' => $image->id(),
        'alt' => $alt,
        'title' => $title,
      ],
    ]);
    $entity->save();

    $object = ImageValueObject::fromStyledImageItem($entity->get('field_image')->first(), $style->getName());
    $this->assertEquals($title, $object->getName());
    $this->assertEquals($alt, $object->getAlt());
    $this->assertContains('/styles/main_style/public/example_1.jpg', $object->getSource());
  }

}
