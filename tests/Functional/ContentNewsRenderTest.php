<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests that our News content type renders correctly.
 *
 * @group batch1
 */
class ContentNewsRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_theme_helper',
    'path',
    'oe_theme_content_news',
    'oe_theme_content_entity_contact',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->markTestSkipped('Skip this test temporarily, as part of ECL v3 upgrade.');

    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests that the News page renders correctly.
   */
  public function testNewsRendering(): void {
    // Create a News node.
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_news',
      'title' => 'Test news node',
      'oe_news_types' => 'http://publications.europa.eu/resource/authority/resource-type/ARTICLE_NEWS',
      'oe_teaser' => 'News teaser',
      'oe_summary' => 'http://www.example.org is a web page',
      'body' => 'News body',
      'oe_reference_code' => 'News reference',
      'oe_publication_date' => [
        'value' => '2020-09-18',
      ],
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $assert = new PatternPageHeaderAssert();
    $expected_values = [
      'title' => 'Test news node',
      'description' => 'http://www.example.org is a web page',
      'meta' => 'News article | 18 September 2020 | African Court of Justice and Human Rights',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // The default text format should be applied, converting URLs into links.
    $header_link = $this->assertSession()->elementExists('css', '.ecl-page-header-core__description a');
    $this->assertEquals('http://www.example.org', $header_link->getAttribute('href'));
    $this->assertEquals('http://www.example.org', $header_link->getText());

    $node->set('oe_news_location', 'http://publications.europa.eu/resource/authority/place/ARE_AUH');
    $node->set('oe_news_types', 'http://publications.europa.eu/resource/authority/resource-type/PUB_GEN');
    $node->save();
    $this->drupalGet($node->toUrl());
    $expected_values['meta'] = 'General publications | 18 September 2020 | Abu Dhabi | African Court of Justice and Human Rights';
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Assert news details.
    $details = $this->assertSession()->elementExists('css', 'div#news-details');
    $field_list_assert = new FieldListAssert();
    $details_expected_values = [
      'items' => [
        [
          'label' => 'Reference',
          'body' => 'News reference',
        ],
        [
          'label' => 'Publication date',
          'body' => '18 September 2020',
        ],
        [
          'label' => 'Author',
          'body' => 'African Court of Justice and Human Rights',
        ],
        [
          'label' => 'Location',
          'body' => 'Abu Dhabi',
        ],
      ],
    ];
    $details_html = $details->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);
    $field_list_assert->assertVariant('horizontal', $details_html);

    // Assert Author field label.
    $node->set('oe_author', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ACJHR'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ACP_CDE'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $details_expected_values['items'][2]['label'] = 'Authors';
    $details_expected_values['items'][2]['body'] = 'African Court of Justice and Human Rights | Centre for the Development of Enterprise';
    $details_html = $details->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);

    // Assert Body field.
    $body = $this->assertSession()->elementExists('css', 'article[role=article] .ecl-editor');
    $this->assertEquals('News body', $body->getText());

    // Assert news contacts.
    $contact = $this->createContactEntity('news_contact');
    $node->set('oe_news_contacts', [$contact])->save();
    $this->drupalGet($node->toUrl());

    $contacts_content = $this->assertSession()->elementExists('css', 'div#news-contacts');
    $this->assertContentHeader($contacts_content, 'Contacts');
    $this->assertContactDefaultRender($contacts_content, 'news_contact');

    // Add a different and unpublished media and assert it is not rendered
    // in the contact.
    $media = $this->getStorage('media')->loadByProperties(['name' => 'Test image news_contact']);
    $media = reset($media);
    $media->set('status', 0)->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', 'div#news-contacts div figure.ecl-media-container img');

    // Assert Featured media field.
    $this->assertSession()->elementNotExists('css', 'article[role=article] article.ecl-u-type-paragraph.ecl-u-mb-l');

    $media = $this->createMediaImage('news_featured_media');
    $node->set('oe_news_featured_media', [$media])->save();
    $this->drupalGet($node->toUrl());

    $picture = $this->assertSession()->elementExists('css', 'article[role=article] article.ecl-u-type-paragraph.ecl-u-mb-l picture');
    $image = $this->assertSession()->elementExists('css', 'img.ecl-u-width-100.ecl-u-height-auto', $picture);
    $this->assertContains('placeholder_news_featured_media.png', $image->getAttribute('src'));
    $this->assertEquals('Alternative text news_featured_media', $image->getAttribute('alt'));

    // Unpublish the media and assert it is not rendered anymore.
    $media->set('status', 0);
    $media->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementNotExists('css', 'article[role=article] article.ecl-u-type-paragraph.ecl-u-mb-l picture');

    // Publish the media.
    $media->set('status', 1);
    $media->save();

    // Assert related links.
    $node->set('oe_related_links', [
      [
        'uri' => 'internal:/node',
        'title' => 'Node listing',
      ], [
        'uri' => 'https://example.com',
        'title' => 'External link',
      ],
    ])->save();
    $this->drupalGet($node->toUrl());

    $related_links_header = $this->assertSession()->elementExists('css', 'article[role=article] > div > h2');
    $this->assertContentHeader($related_links_header, 'Related links');
    $related_links_content = $this->assertSession()->elementExists('css', 'article[role=article] > div > div.ecl-list article.ecl-content-item:nth-child(1)');
    $link_assert = new ListItemAssert();
    $link_expected_values = [
      'url' => '/build/node',
      'title' => 'Node listing',
    ];
    $link_assert->assertPattern($link_expected_values, $related_links_content->getOuterHtml());
    $link_assert->assertVariant('default', $related_links_content->getOuterHtml());

    $related_links_content = $this->assertSession()->elementExists('css', 'article[role=article] > div > div.ecl-list article.ecl-content-item:nth-child(2)');
    $link_expected_values = [
      'url' => 'https://example.com',
      'title' => 'External link',
    ];
    $link_assert->assertPattern($link_expected_values, $related_links_content->getOuterHtml());
    $link_assert->assertVariant('default', $related_links_content->getOuterHtml());
  }

}
