<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\Component\Utility\UrlHelper;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\oe_theme\ValueObject\MediaValueObject;
use Drupal\Tests\token\Kernel\KernelTestBase;

/**
 * Test media value object with image and video sources.
 */
class MediaValueObjectTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'image',
    'file',
    'field',
    'media',
    'oe_media',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('file', 'file_usage');
    $this->installEntitySchema('user');
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');
    $this->installConfig([
      'user',
      'media',
      'image',
      'file',
      'field',
      'system',
      'oe_media',
    ]);

    // Copy file in public files to use it for styling.
    \Drupal::service('file_system')->copy(__DIR__ . '/../../fixtures/example_1.jpeg', 'public://example_1.jpg');
  }

  /**
   * Test the media value object has the correct values returned.
   */
  public function testMediaValueObject() {
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
    $alt = $this->randomString();
    $title = $this->randomString();

    $media = \Drupal::entityTypeManager()
      ->getStorage('media')->create([
        'bundle' => 'image',
        'name' => $title,
        'oe_media_image' => [
          'target_id' => (int) $image->id(),
          'alt' => $alt,
        ],
        'status' => 1,
      ]);
    $media->save();

    $object = MediaValueObject::fromMediaObject($media, 'main_style');
    $image_value_object = $object->getImage();
    $this->assertInstanceOf('\Drupal\oe_theme\ValueObject\ImageValueObject', $image_value_object);
    $this->assertEquals($alt, $image_value_object->getAlt());
    $this->assertContains('/styles/main_style/public/example_1.jpg', $image_value_object->getSource());

    $remote_video = 'https://www.youtube.com/watch?v=OkPW9mK5Vw8';
    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'remote_video',
        'oe_media_oembed_video' => $remote_video,
        'status' => 1,
      ]);
    $media->save();

    $object = MediaValueObject::fromMediaObject($media);
    $this->assertContains(UrlHelper::encodePath($remote_video), $object->getVideo());
  }

}
