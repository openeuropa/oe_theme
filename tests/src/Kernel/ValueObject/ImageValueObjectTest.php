<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\Tests\token\Kernel\TokenKernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\oe_theme\ValueObject\ImageValueObject;

/**
 * Test image value object with image field type.
 *
 * @group batch2
 */
class ImageValueObjectTest extends TokenKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'image',
    'file',
    'entity_test',
    'field',
  ];

  /**
   * Entity object.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
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
    \Drupal::service('file_system')->copy(__DIR__ . '/../../../fixtures/example_1.jpeg', 'public://example_1.jpg');

    // Create image file.
    $image = File::create([
      'uri' => 'public://example_1.jpg',
    ]);
    $image->save();

    // Create an image item.
    $this->entity = EntityTest::create([
      'name' => $this->randomString(),
      'field_image' => [
        'target_id' => $image->id(),
        'alt' => 'This is an alternative title',
        'title' => 'This is a title',
      ],
    ]);
    $this->entity->save();
  }

  /**
   * Tests the ::fromStyledImageItem method.
   */
  public function testFromStyledImageItem() {
    // Create a test style.
    /** @var \Drupal\image\ImageStyleInterface $style */
    $style = ImageStyle::create(['name' => 'main_style']);
    $style->save();

    $object = ImageValueObject::fromStyledImageItem($this->entity->get('field_image')->first(), $style->getName());
    $this->assertEquals('This is a title', $object->getName());
    $this->assertEquals('This is an alternative title', $object->getAlt());
    $this->assertStringContainsString('/styles/main_style/public/example_1.jpg', $object->getSource());

    // Test that all the cache tags are present and have bubbled up.
    $this->assertEqualsCanonicalizing([
      'config:image.style.main_style',
      'file:1',
    ], $object->getCacheTags());

    $invalid_image_style = $this->randomMachineName();
    $this->expectExceptionObject(new \InvalidArgumentException(sprintf('Could not load image style with name "%s".', $invalid_image_style)));
    ImageValueObject::fromStyledImageItem($this->entity->get('field_image')->first(), $invalid_image_style);
  }

  /**
   * Tests the ::fromImageItem method.
   */
  public function testFromImageItem() {
    $object = ImageValueObject::fromImageItem($this->entity->get('field_image')->first());
    $this->assertEquals('This is a title', $object->getName());
    $this->assertEquals('This is an alternative title', $object->getAlt());
    $this->assertStringContainsString('/files/example_1.jpg', $object->getSource());

    // Test that all the cache tags are present and have bubbled up.
    $this->assertEqualsCanonicalizing([
      'file:1',
    ], $object->getCacheTags());
  }

}
