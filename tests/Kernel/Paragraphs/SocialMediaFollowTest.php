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
      'field_oe_social_media_see_more' => [
        'title' => 'Other social networks',
        'uri' => 'https://europa.eu/european-union/contact/social-networks_en',
      ],
    ]);
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify the horizontal layout (default layout in ecl).
    $this->assertCount(1, $crawler->filter('.ecl-social-media-follow'));

    // Change variant to vertical layout.
    $paragraph->get('field_oe_social_media_variant')->setValue('vertical');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify the vertical layout.
    $this->assertCount(1, $crawler->filter('.ecl-social-media-follow.ecl-social-media-follow--vertical'));

    // Verify that links are rendered.
    $links = $crawler->filter('.ecl-social-media-follow__list');
    $this->assertCount(1, $links);

    // Verify that the paragraph contains all the links.
    $links_html = $links->html();
    $this->assertContains('Email', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 5) = \'#email\']'));
    // Assert that the Facebook link is rendered but not the Facebook icon, as
    // this link has no type associated.
    $this->assertContains('Facebook', $links_html);
    $this->assertCount(0, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 8) = \'#facebook\']'));
    $this->assertContains('Flickr', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#flickr\']'));
    $this->assertContains('Google+', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#google\']'));
    $this->assertContains('Instagram', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 9) = \'#instagram\']'));
    $this->assertContains('LinkedIn', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 8) = \'#linkedin\']'));
    $this->assertContains('Pinterest', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 9) = \'#pinterest\']'));
    $this->assertContains('RSS', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 3) = \'#rss\']'));
    $this->assertContains('Storify', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#storify\']'));
    $this->assertContains('1st Twitter', $links_html);
    $this->assertContains('2nd Twitter', $links_html);
    $this->assertCount(2, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#twitter\']'));
    $this->assertContains('Yammer', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 6) = \'#yammer\']'));
    $this->assertContains('Youtube', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 7) = \'#youtube\']'));
    $this->assertContains('Other social networks', $links_html);

    // Fix the Facebook link type.
    $paragraph->get('field_oe_social_media_links')->get(1)->set('link_type', 'facebook');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify that the Facebook icon is now rendered.
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 8) = \'#facebook\']'));
  }

  /**
   * Returns a list of link items for Social media follow paragraph.
   *
   * @return array
   *   An array of link items.
   */
  protected function getSocialMediaLinks(): array {
    return [
      [
        'title' => 'Email',
        'uri' => 'mailto:example@com',
        'link_type' => 'email',
      ],
      // Add a Facebook link without type.
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
  }

}
