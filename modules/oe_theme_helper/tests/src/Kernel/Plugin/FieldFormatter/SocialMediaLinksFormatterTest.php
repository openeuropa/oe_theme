<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test social media link formatter.
 */
class SocialMediaLinksFormatterTest extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'link',
    'typed_link',
    'entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('entity_test');
  }

  /**
   * Test social media links formatting.
   */
  public function testFormatter() {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_test',
      'entity_type' => 'entity_test',
      'type' => 'typed_link',
      'settings' => [
        'allowed_values' => [
          'facebook' => 'Facebook',
          'twitter' => 'Twitter',
        ],
      ],
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'entity_test',
      'label' => 'Test field',
    ]);
    $field->save();

    $entity = EntityTest::create([
      'field_test' => [
        'link_type' => 'facebook',
        'uri' => 'http://facebook.com',
        'title' => 'Facebook',
      ],
    ]);
    $entity->save();

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('entity_test');

    // Test formatter with default settings.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_social_media_links_formatter',
    ]);
    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        'a.ecl-social-media-follow__link[href="http://facebook.com"]' => 1,
      ],
      'equals' => [
        '.ecl-social-media-follow > p.ecl-social-media-follow__description' => 'Social media',
        'a.ecl-social-media-follow__link[href="http://facebook.com"] span' => "Facebook",
      ],
    ]);

    // Test formatter with custom settings.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_social_media_links_formatter',
      'settings' => [
        'title' => 'View European Commission on:',
        'variant' => 'vertical',
      ],
    ]);
    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        'a.ecl-social-media-follow__link[href="http://facebook.com"]' => 1,
      ],
      'equals' => [
        '.ecl-social-media-follow--vertical > p.ecl-social-media-follow__description' => 'View European Commission on:',
        'a.ecl-social-media-follow__link[href="http://facebook.com"] span' => "Facebook",
      ],
    ]);
  }

}
