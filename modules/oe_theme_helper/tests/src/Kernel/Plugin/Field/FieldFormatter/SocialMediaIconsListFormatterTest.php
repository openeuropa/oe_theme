<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

/**
 * Test "Social media icons list" formatter.
 *
 * @group batch2
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
        'a.ecl-link[href="mailto:socialmedialink@example.com"]' => 1,
        'a.ecl-link[href="http://facebook.com"]' => 1,
        'a.ecl-link[href="http://twitter.com"]' => 1,
        'a.ecl-link[href="http://t.me/example"]' => 1,
        'a.ecl-link[href="http://mastodon.social/@example"]' => 1,
      ],
      'equals' => [
        'a.ecl-link[href="mailto:socialmedialink@example.com"] span' => "Email",
        'a.ecl-link[href="http://facebook.com"] span' => "Facebook",
        'a.ecl-link[href="http://twitter.com"] span' => "Twitter",
        'a.ecl-link[href="http://t.me/example"] span' => "Telegram",
        'a.ecl-link[href="http://mastodon.social/@example"] span' => "Mastodon",
      ],
      'contains' => [
        'a.ecl-link[href="mailto:socialmedialink@example.com"] use' => 'icons-social.svg#twitter',
        'a.ecl-link[href="http://facebook.com"] use' => 'icons-social.svg#facebook',
        'a.ecl-link[href="http://twitter.com"] use' => 'icons-social.svg#twitter',
        'a.ecl-link[href="http://t.me/example"] use' => 'icons-social.svg#telegram',
        'a.ecl-link[href="http://mastodon.social/@example"] use' => 'icons-social.svg#mastodon',
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
        'a.ecl-link[href="mailto:socialmedialink@example.com"]' => 1,
        'a.ecl-link[href="http://facebook.com"]' => 1,
        'a.ecl-link[href="http://twitter.com"]' => 1,
        'a.ecl-link[href="http://t.me/example"]' => 1,
        'a.ecl-link[href="http://mastodon.social/@example"]' => 1,
      ],
      'equals' => [
        'a.ecl-link[href="mailto:socialmedialink@example.com"] span' => "Email",
        'a.ecl-link[href="http://facebook.com"] span' => "Face…",
        'a.ecl-link[href="http://twitter.com"] span' => "Twit…",
        'a.ecl-link[href="http://t.me/example"] span' => "Tele…",
        'a.ecl-link[href="http://mastodon.social/@example"] span' => "Mast…",
      ],
      'contains' => [
        'a.ecl-link[href="mailto:socialmedialink@example.com"] use' => 'icons-social.svg#email',
        'a.ecl-link[href="http://facebook.com"] use' => 'icons-social.svg#facebook',
        'a.ecl-link[href="http://twitter.com"] use' => 'icons-social.svg#twitter',
        'a.ecl-link[href="http://t.me/example"] use' => 'icons-social.svg#telegram',
        'a.ecl-link[href="http://mastodon.social/@example"] use' => 'icons-social.svg#mastodon',
      ],
    ]);
  }

}
