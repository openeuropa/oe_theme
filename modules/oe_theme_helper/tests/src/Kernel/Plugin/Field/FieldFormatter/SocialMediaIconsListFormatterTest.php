<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

/**
 * Test "Social media icons list" formatter.
 */
class SocialMediaIconsListFormatterTest extends SocialMediaLinksFormatterTestBase {

  /**
   * Test social media links formatting.
   */
  public function testFormatter() {
    $entity = $this->createEntityTest();
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('entity_test');

    // Test formatter with default settings.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_social_media_icons_list_formatter',
    ]);
    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        'a.ecl-link[href="http://facebook.com"]' => 1,
      ],
      'equals' => [
        'a.ecl-link[href="http://facebook.com"] span' => "Facebook",
      ],
      'contains' => [
        'a.ecl-link[href="http://facebook.com"] use' => 'icons-social.svg#facebook',
      ],
    ]);

    // Test formatter with custom settings.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_social_media_icons_list_formatter',
      'settings' => [
        'trim_length' => '5',
      ],
    ]);
    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        'a.ecl-link[href="http://facebook.com"]' => 1,
      ],
      'equals' => [
        'a.ecl-link[href="http://facebook.com"] span' => "Face…",
      ],
      'contains' => [
        'a.ecl-link[href="http://facebook.com"] use' => 'icons-social.svg#facebook',
      ],
    ]);
  }

}
