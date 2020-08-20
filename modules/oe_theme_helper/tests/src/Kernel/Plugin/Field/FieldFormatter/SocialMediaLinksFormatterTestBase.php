<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Base test class for "Social media links" field formatters.
 */
class SocialMediaLinksFormatterTestBase extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
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
   * Creates entity for testing purposes.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity test instance.
   */
  protected function createEntityTest(): EntityInterface {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_test',
      'entity_type' => 'entity_test',
      'type' => 'typed_link',
      'settings' => [
        'allowed_values' => [
          'email' => 'Email',
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
        [
          'link_type' => 'email',
          'uri' => 'mailto:socialmedialink@example.com',
          'title' => 'Email',
        ],
        [
          'link_type' => 'facebook',
          'uri' => 'http://facebook.com',
          'title' => 'Facebook',
        ],
        [
          'link_type' => 'twitter',
          'uri' => 'http://twitter.com',
          'title' => 'Twitter',
        ],
      ],
    ]);
    $entity->save();

    return $entity;
  }

}
