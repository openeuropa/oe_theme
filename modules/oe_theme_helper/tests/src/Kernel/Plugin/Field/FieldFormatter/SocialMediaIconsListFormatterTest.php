<?php

declare(strict_types=1);

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
        'a.ecl-link[href="mailto:socialmedialink@example.com"] span.wt-icon--email.ecl-social-media-follow__icon' => 1,
        'a.ecl-link[href="http://facebook.com"] span.wt-icon--facebook.ecl-social-media-follow__icon' => 1,
        'a.ecl-link[href="http://twitter.com"] span.wt-icon--twitter.ecl-social-media-follow__icon' => 1,
        'a.ecl-link[href="http://t.me/example"] span.wt-icon--telegram.ecl-social-media-follow__icon' => 1,
        'a.ecl-link[href="http://mastodon.social/@example"] span.wt-icon--mastodon.ecl-social-media-follow__icon' => 1,
      ],
      'equals' => [
        'a.ecl-link[href="mailto:socialmedialink@example.com"] span.ecl-link__label' => "Email",
        'a.ecl-link[href="http://facebook.com"] span.ecl-link__label' => "Facebook",
        'a.ecl-link[href="http://twitter.com"] span.ecl-link__label' => "Twitter",
        'a.ecl-link[href="http://t.me/example"] span.ecl-link__label' => "Telegram",
        'a.ecl-link[href="http://mastodon.social/@example"] span.ecl-link__label' => "Mastodon",
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
        'a.ecl-link[href="mailto:socialmedialink@example.com"] span.wt-icon--email.ecl-social-media-follow__icon' => 1,
        'a.ecl-link[href="http://facebook.com"] span.wt-icon--facebook.ecl-social-media-follow__icon' => 1,
        'a.ecl-link[href="http://twitter.com"] span.wt-icon--twitter.ecl-social-media-follow__icon' => 1,
        'a.ecl-link[href="http://t.me/example"] span.wt-icon--telegram.ecl-social-media-follow__icon' => 1,
        'a.ecl-link[href="http://mastodon.social/@example"] span.wt-icon--mastodon.ecl-social-media-follow__icon' => 1,
      ],
      'equals' => [
        'a.ecl-link[href="mailto:socialmedialink@example.com"] span.ecl-link__label' => "Email",
        'a.ecl-link[href="http://facebook.com"] span.ecl-link__label' => "Face…",
        'a.ecl-link[href="http://twitter.com"] span.ecl-link__label' => "Twit…",
        'a.ecl-link[href="http://t.me/example"] span.ecl-link__label' => "Tele…",
        'a.ecl-link[href="http://mastodon.social/@example"] span.ecl-link__label' => "Mast…",
      ],
    ]);
  }

}
