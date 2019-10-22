<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Social media follow" paragraph.
 */
class SocialMediaFollowTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'user',
    'options',
    'field',
    'link',
    'typed_link',
    'system',
    'oe_paragraphs',
  ];

  /**
   * Tests the rendering of the paragraph type.
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function testRendering(): void {
    // Create social media link items.
    $items = [
      [
        'title' => 'Facebook',
        'uri' => 'https://facebook.com',
        'link_type' => 'Facebook',
      ],
      [
        'title' => 'Flickr',
        'uri' => 'https://www.flickr.com',
        'link_type' => 'Flickr',
      ],
      [
        'title' => 'Google+',
        'uri' => 'https://google.com',
        'link_type' => 'Google+',
      ],
      [
        'title' => 'Instagram',
        'uri' => 'https://instagram.com',
        'link_type' => 'Instagram',
      ],
      [
        'title' => 'LinkedIn',
        'uri' => 'https://linkedin.com',
        'link_type' => 'linkedin',
      ],
      [
        'title' => 'Pinterest',
        'uri' => 'https://pinterest.com',
        'link_type' => 'Pinterest',
      ],
      [
        'title' => 'RSS',
        'uri' => 'https://rss-example.com',
        'link_type' => 'RSS',
      ],
      [
        'title' => 'Storify',
        'uri' => 'https://storify.com',
        'link_type' => 'Storify',
      ],
      [
        'title' => 'Twitter',
        'uri' => 'https://twitter.com',
        'link_type' => 'Twitter',
      ],
      [
        'title' => 'Yammer',
        'uri' => 'https://yammer.com',
        'link_type' => 'Yammer',
      ],
      [
        'title' => 'Youtube',
        'uri' => 'https://youtube.com',
        'link_type' => 'YouTube',
      ],
    ];

    // Create social media follow paragraph with horizontal variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_social_media_follow',
      'field_oe_title' => 'Social media title',
      'field_oe_social_media_variant' => 'horizontal',
      'field_oe_social_media_links' => $items,
    ]);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify that there is a horizontal layout.
    $this->assertCount(1, $crawler->filter('.ecl-social-media-link.ecl-social-media-link--horizontal'));

    // Change variant to vertical layout.
    $paragraph->get('field_oe_social_media_variant')->setValue('vertical');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify the vertical layout.
    $this->assertCount(0, $crawler->filter('.ecl-social-media-link--horizontal'));
    $this->assertCount(1, $crawler->filter('.ecl-social-media-link'));

    // Verify that links are rendered.
    $links = $crawler->filter('.ecl-social-media-link__list.ecl-list.ecl-list--unstyled');
    $this->assertCount(1, $links);

    // Verify that the paragraph contains all the links.
    $links_html = $links->html();
    $this->assertContains('Facebook', $links_html);
    $this->assertContains('Flickr', $links_html);
    $this->assertContains('Google+', $links_html);
    $this->assertContains('Instagram', $links_html);
    $this->assertContains('LinkedIn', $links_html);
    $this->assertContains('Pinterest', $links_html);
    $this->assertContains('RSS', $links_html);
    $this->assertContains('Storify', $links_html);
    $this->assertContains('Twitter', $links_html);
    $this->assertContains('Yammer', $links_html);
    $this->assertContains('Youtube', $links_html);
  }

}
