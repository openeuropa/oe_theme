<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\Core\Url;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
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
  protected static $modules = [
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
      'oe_departments' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header.ecl-page-header--negative');
    $assert = new PatternPageHeaderAssert();
    $expected_values = [
      'title' => 'Test news node',
      'description' => 'http://www.example.org is a web page',
      'meta' => [
        'News article',
        '18 September 2020',
        'African Court of Justice and Human Rights',
      ],
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // The default text format should be applied, converting URLs into links.
    $header_link = $this->assertSession()->elementExists('css', '.ecl-page-header__description a');
    $this->assertEquals('http://www.example.org', $header_link->getAttribute('href'));
    $this->assertEquals('http://www.example.org', $header_link->getText());

    // Assert Sources field content.
    $node->set('oe_news_sources', [
      [
        'uri' => 'internal:/node',
        'title' => 'Internal source link',
      ], [
        'uri' => 'https://example.com',
        'title' => 'External source link',
      ],
    ])->save();
    $this->drupalGet($node->toUrl());
    $this->assertEquals('Sources', $this->assertSession()->elementExists('css', 'h3.ecl-u-type-heading-3')->getText());
    $internal_source_link = $this->assertSession()->elementExists('css', 'div.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-pt-m.ecl-u-pb-m:nth-child(3)');
    $this->assertLinkIcon($internal_source_link, 'Internal source link', '/build/node', FALSE, 'xs');
    $external_source_link = $this->assertSession()->elementExists('css', 'div.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-pt-m.ecl-u-pb-m:nth-child(4)');
    $this->assertLinkIcon($external_source_link, 'External source link', 'https://example.com', TRUE, 'xs');

    $node->set('oe_news_location', 'http://publications.europa.eu/resource/authority/place/ARE_AUH');
    $node->set('oe_news_types', 'http://publications.europa.eu/resource/authority/resource-type/PUB_GEN');
    $node->save();
    $this->drupalGet($node->toUrl());
    $expected_values['meta'] = [
      'General publications',
      '18 September 2020',
      'Abu Dhabi',
      'African Court of Justice and Human Rights',
    ];
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
          'label' => 'Department',
          'body' => 'Associated African States and Madagascar',
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

    // Assert Last update date field.
    $node->set('oe_news_last_updated', '2021-08-04')->save();
    $this->drupalGet($node->toUrl());

    $details_expected_values['items'][1]['body'] = '18 September 2020 (Last updated on: 4 August 2021)';
    $details_html = $details->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);

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

    // Assert Departments field label.
    $node->set('oe_departments', [
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM'],
      ['target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC'],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());
    $details_expected_values['items'][3]['label'] = 'Departments';
    $details_expected_values['items'][3]['body'] = 'Associated African States and Madagascar, Audit Board of the European Communities';
    $details_html = $details->getHtml();
    $field_list_assert->assertPattern($details_expected_values, $details_html);

    // Assert Body field.
    $body = $this->assertSession()->elementExists('css', 'article[role=article] .ecl');
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

    // Create a remote video and add it to the node.
    $media = $this->getStorage('media')->create([
      'bundle' => 'remote_video',
      'oe_media_oembed_video' => [
        'value' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ]);
    $media->save();
    $node->set('oe_news_featured_media', [$media])->save();
    $this->drupalGet($node->toUrl());
    $media_container = $this->assertSession()->elementExists('css', 'article.ecl-u-type-paragraph.ecl-u-mb-l figure.ecl-media-container');
    $video = $this->assertSession()->elementExists('css', 'div.ecl-media-container__media iframe', $media_container);
    $partial_video_url = Url::fromRoute('media.oembed_iframe', [], [
      'query' => [
        'url' => 'https://www.youtube.com/watch?v=1-g73ty9v04',
      ],
    ])->toString();
    $this->assertStringContainsString($partial_video_url, $video->getAttribute('src'));
    $this->assertStringContainsString('200', $video->getAttribute('width'));
    $this->assertStringContainsString('150', $video->getAttribute('height'));

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
    $this->assertEquals('Related links', $this->assertSession()->elementExists('css', 'h2.ecl-u-type-heading-2:nth-child(8)')->getText());
    $first_related_link = $this->assertSession()->elementExists('css', 'div.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-pt-m.ecl-u-pb-m:nth-child(9) a');
    $this->assertEquals('/build/node', $first_related_link->getAttribute('href'));
    $this->assertEquals('Node listing', $first_related_link->getText());
    $second_related_link = $this->assertSession()->elementExists('css', 'div.ecl-u-border-bottom.ecl-u-border-color-grey-15.ecl-u-pt-m.ecl-u-pb-m:nth-child(10) a');
    $this->assertEquals('https://example.com', $second_related_link->getAttribute('href'));
    $this->assertEquals('External link', $second_related_link->getText());
  }

}
