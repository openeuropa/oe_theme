<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Social media follow" paragraph.
 *
 * @group batch2
 */
class SocialMediaFollowTest extends ParagraphsTestBase {

  /**
   * Tests the rendering of the paragraph type.
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function testRendering(): void {
    $this->markTestSkipped('Must be re-enabled before considering migration to ECL 4 as complete.');
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
    $this->assertStringContainsString('Email', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 11) = \'#email-color\']'));
    // Assert that the Facebook link is rendered but not the Facebook icon, as
    // this link has no type associated.
    $this->assertStringContainsString('Facebook', $links_html);
    $this->assertCount(0, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 14) = \'#facebook-color\']'));
    $this->assertStringContainsString('Flickr', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 12) = \'#flickr-color\']'));
    $this->assertStringContainsString('Google+', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 12) = \'#google-color\']'));
    $this->assertStringContainsString('Instagram', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 15) = \'#instagram-color\']'));
    $this->assertStringContainsString('LinkedIn', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 14) = \'#linkedin-color\']'));
    $this->assertStringContainsString('Pinterest', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 15) = \'#pinterest-color\']'));
    $this->assertStringContainsString('RSS', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 9) = \'#rss-color\']'));
    $this->assertStringContainsString('Storify', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 13) = \'#storify-color\']'));
    $this->assertStringContainsString('1st Twitter', $links_html);
    $this->assertStringContainsString('2nd Twitter', $links_html);
    $this->assertCount(2, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 13) = \'#twitter-color\']'));
    $this->assertStringContainsString('Yammer', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 12) = \'#yammer-color\']'));
    $this->assertStringContainsString('Youtube', $links_html);
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 13) = \'#youtube-color\']'));
    $this->assertStringContainsString('Other social networks', $links_html);

    // Fix the Facebook link type.
    $paragraph->get('field_oe_social_media_links')->get(1)->set('link_type', 'facebook');
    $paragraph->save();
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    // Verify that the Facebook icon is now rendered.
    $this->assertCount(1, $crawler->filterXPath('//*[name()=\'use\' and substring(@*, string-length(@*) - 14) = \'#facebook-color\']'));
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
