<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\ValueObject;

use Drupal\Component\Utility\UrlHelper;
use Drupal\image\Entity\ImageStyle;
use Drupal\oe_theme\ValueObject\MediaValueObject;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\Tests\token\Kernel\KernelTestBase;

/**
 * Test media value object with image and video sources.
 */
class MediaValueObjectTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'field',
    'file',
    'media',
    'image',
    'oe_media',
    'file_link',
    'link',
    'options',
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
      'file',
      'media',
      'oe_media',
    ]);
  }

  /**
   * Test the media value object has the correct values returned.
   */
  public function testMediaValueObject() {
    // Create image file.
    $image = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $image->setPermanent();
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

    $media_value_object = MediaValueObject::fromMediaObject($media, 'main_style');
    $this->assertInstanceOf(MediaValueObject::class, $media_value_object);
    $image_value_object = $media_value_object->getImage();
    $this->assertInstanceOf(ImageValueObject::class, $image_value_object);
    $this->assertEquals($alt, $image_value_object->getAlt());
    $this->assertContains('/styles/main_style/public/example_1.jpeg', $image_value_object->getSource());

    $remote_video = 'https://www.youtube.com/watch?v=OkPW9mK5Vw8';
    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')->create([
        'bundle' => 'remote_video',
        'oe_media_oembed_video' => $remote_video,
        'status' => 1,
      ]);
    $media->save();

    $media_value_object = MediaValueObject::fromMediaObject($media);
    $this->assertInstanceOf(MediaValueObject::class, $media_value_object);
    $this->assertContains(UrlHelper::encodePath($remote_video), $media_value_object->getVideo());
  }

}
