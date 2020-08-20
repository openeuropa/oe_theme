<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

/**
 * Test social media link formatter.
 */
class SocialMediaLinksFormatterTest extends SocialMediaLinksFormatterTestBase {

  /**
   * Test social media links formatting.
   */
  public function testFormatter() {
    $entity = $this->createEntityTest();
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('entity_test');

    // Test formatter with default settings.
    $build = $view_builder->viewField($entity->get('field_test'), [
      'type' => 'oe_theme_helper_social_media_links_formatter',
    ]);
    $this->assertRendering($this->renderRoot($build), [
      'count' => [
        'a.ecl-social-media-follow__link[href="mailto:socialmedialink@example.com"]' => 1,
        'a.ecl-social-media-follow__link[href="http://facebook.com"]' => 1,
        'a.ecl-social-media-follow__link[href="http://twitter.com"]' => 1,
      ],
      'equals' => [
        '.ecl-social-media-follow > p.ecl-social-media-follow__description' => 'Social media',
        'a.ecl-social-media-follow__link[href="mailto:socialmedialink@example.com"] span' => "Email",
        'a.ecl-social-media-follow__link[href="http://facebook.com"] span' => "Facebook",
        'a.ecl-social-media-follow__link[href="http://twitter.com"] span' => "Twitter",
      ],
      'contains' => [
        'a.ecl-social-media-follow__link[href="mailto:socialmedialink@example.com"] use' => 'icons-social.svg#email',
        'a.ecl-social-media-follow__link[href="http://facebook.com"] use' => 'icons-social.svg#facebook',
        'a.ecl-social-media-follow__link[href="http://twitter.com"] use' => 'icons-social.svg#twitter',
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
        'a.ecl-social-media-follow__link[href="mailto:socialmedialink@example.com"]' => 1,
        'a.ecl-social-media-follow__link[href="http://facebook.com"]' => 1,
        'a.ecl-social-media-follow__link[href="http://twitter.com"]' => 1,
      ],
      'equals' => [
        '.ecl-social-media-follow--vertical > p.ecl-social-media-follow__description' => 'View European Commission on:',
        'a.ecl-social-media-follow__link[href="mailto:socialmedialink@example.com"] span' => "Email",
        'a.ecl-social-media-follow__link[href="http://facebook.com"] span' => "Facebook",
        'a.ecl-social-media-follow__link[href="http://twitter.com"] span' => "Twitter",
      ],
      'contains' => [
        'a.ecl-social-media-follow__link[href="mailto:socialmedialink@example.com"] use' => 'icons-social.svg#email',
        'a.ecl-social-media-follow__link[href="http://facebook.com"] use' => 'icons-social.svg#facebook',
        'a.ecl-social-media-follow__link[href="http://twitter.com"] use' => 'icons-social.svg#twitter',
      ],
    ]);
  }

}
