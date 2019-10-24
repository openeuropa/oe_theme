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
    'typed_link',
  ];

  /**
   * Creates link items for Social media follow paragraph.
   *
   * @return array
   *   Returns array of link items.
   */
  protected function getSocialMediaLinks(): array {
    // Create social media link items.
    $items = [
      [
        'title' => 'Email',
        'uri' => 'mailto:example@com',
        'link_type' => 'email',
      ],
      [
        'title' => 'Facebook',
        'uri' => 'https://facebook.com',
        'link_type' => '',
      ],
      [
        'title' => 'Flickr',
        'uri' => 'https://www.flickr.com',
        'link_type' => 'flickr',
      ],
      [
        'title' => 'Google+',
        'uri' => 'https://google.com',
        'link_type' => 'google',
      ],
      [
        'title' => 'Instagram',
        'uri' => 'https://instagram.com',
        'link_type' => 'instagram',
      ],
      [
        'title' => 'LinkedIn',
        'uri' => 'https://linkedin.com',
        'link_type' => 'linkedin',
      ],
      [
        'title' => 'Pinterest',
        'uri' => 'https://pinterest.com',
        'link_type' => 'pinterest',
      ],
      [
        'title' => 'RSS',
        'uri' => 'https://rss-example.com',
        'link_type' => 'rss',
      ],
      [
        'title' => 'Storify',
        'uri' => 'https://storify.com',
        'link_type' => 'storify',
      ],
      [
        'title' => '1st Twitter',
        'uri' => 'https://twitter.com',
        'link_type' => 'twitter',
      ],
      [
        'title' => '2nd Twitter',
        'uri' => 'https://twitter.com',
        'link_type' => 'twitter',
      ],
      [
        'title' => 'Yammer',
        'uri' => 'https://yammer.com',
        'link_type' => 'yammer',
      ],
      [
        'title' => 'Youtube',
        'uri' => 'https://youtube.com',
        'link_type' => 'youtube',
      ],
      [
        'title' => 'Vimeo',
        'uri' => 'https://vimeo.com',
        'link_type' => 'vimeo',
      ],
    ];

    return $items;
  }

  /**
   * Tests the rendering of the paragraph type.
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function testRendering(): void {
    // Create social media follow paragraph with horizontal variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_social_media_follow',
      'field_oe_title' => 'Social media title',
      'field_oe_social_media_variant' => 'horizontal',
      'field_oe_social_media_links' => $this->getSocialMediaLinks(),
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

    // Verify that the horizontal layout is not present.
    $this->assertCount(0, $crawler->filter('.ecl-social-media-link--horizontal'));
    // Verify the default ecl layout (vertical layout).
    $this->assertCount(1, $crawler->filter('.ecl-social-media-link'));

    // Verify that links are rendered.
    $links = $crawler->filter('.ecl-social-media-link__list.ecl-list.ecl-list--unstyled');
    $this->assertCount(1, $links);

    // Verify that the paragraph contains all the links.
    $links_html = $links->html();
    $this->assertContains('Email', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--email.ecl-social-media-link__link'));
    $this->assertContains('Facebook', $links_html);
    $this->assertCount(0, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--facebook.ecl-social-media-link__link'));
    $this->assertContains('Flickr', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--flickr.ecl-social-media-link__link'));
    $this->assertContains('Google+', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--google.ecl-social-media-link__link'));
    $this->assertContains('Instagram', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--instagram.ecl-social-media-link__link'));
    $this->assertContains('LinkedIn', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--linkedin.ecl-social-media-link__link'));
    $this->assertContains('Pinterest', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--pinterest.ecl-social-media-link__link'));
    $this->assertContains('RSS', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--rss.ecl-social-media-link__link'));
    $this->assertContains('Storify', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--storify.ecl-social-media-link__link'));
    $this->assertContains('1st Twitter', $links_html);
    $this->assertContains('2nd Twitter', $links_html);
    $this->assertCount(2, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--twitter.ecl-social-media-link__link'));
    $this->assertContains('Yammer', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--yammer.ecl-social-media-link__link'));
    $this->assertContains('Youtube', $links_html);
    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--youtube.ecl-social-media-link__link'));

    // Change variant to vertical layout.
    $links = $paragraph->get('field_oe_social_media_links')->getValue();
    foreach ($links as $key => $link) {
      if ($link['title'] == 'Facebook') {
        $links[$key]['link_type'] = 'facebook';
      }
    }
    $paragraph->get('field_oe_social_media_links')->setValue($links);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('.ecl-link.ecl-social-icon.ecl-social-icon--facebook.ecl-social-media-link__link'));
  }

}
