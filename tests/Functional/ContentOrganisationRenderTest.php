<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\media\Entity\Media;

/**
 * Tests organisation (oe_organisation) content type render.
 */
class ContentOrganisationRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'system',
    'path',
    'node',
    'address',
    'oe_theme_helper',
    'oe_theme_content_entity_contact',
    'oe_theme_content_organisation',
    'page_header_metadata_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Give anonymous users permission to view entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests that the Organisation page renders correctly.
   */
  public function testOrganisationRendering(): void {
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();

    $media = $this->getStorage('media')->create([
      'bundle' => 'image',
      'name' => 'test image',
      'oe_media_image' => [
        'target_id' => $file->id(),
        'alt' => 'Alt',
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getStorage('node')->create([
      'type' => 'oe_organisation',
      'title' => 'My node title',
      'oe_summary' => 'My introduction',
      'oe_organisation_acronym' => 'My acronym',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => 'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'oe_organisation_logo' => [
        'target_id' => $media->id(),
      ],
      'oe_teaser' => 'The teaser text',
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
      'title' => 'My node title',
      'description' => 'My introduction',
      'meta' => 'International organisation | My acronym',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Change organisation type to non eu.
    $node->set('oe_organisation_org_type', 'non_eu');
    $node->set('oe_organisation_non_eu_org_type', 'http://data.europa.eu/uxp/5432');
    $node->save();
    $this->drupalGet($node->toUrl());

    $expected_values['meta'] = 'embassy | My acronym';
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    $logo = $this->assertSession()->elementExists('css', '.ecl-col-lg-3 img.ecl-media-container__media');
    $this->assertContains('styles/oe_theme_medium_no_crop/public/example_1.jpeg', $logo->getAttribute('src'));
    $this->assertEquals('Alt', $logo->getAttribute('alt'));

    // Add body text and contact values.
    $node->set('body', 'My body text');
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert navigation part.
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Description', 'href' => '#description'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Change logo to use av portal image.
    $media = Media::create([
      'bundle' => 'av_portal_photo',
      'oe_media_avportal_photo' => 'P-038924/00-15',
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();
    $file = $media->get('thumbnail')->entity;

    $node->set('oe_organisation_logo', [
      [
        'target_id' => (int) $media->id(),
      ],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    $logo = $this->assertSession()->elementExists('css', '.ecl-col-lg-3 img.ecl-media-container__media');
    $this->assertContains('files/styles/oe_theme_medium_no_crop/public/media_avportal_thumbnails/' . $file->getFilename(), $logo->getAttribute('src'));

    // Assert content part.
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-lg-9', 1);
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(1, $content_items);

    // Assert header of first field group.
    $this->assertContentHeader($content_items[0], 'Description', 'description');

    // Assert values for first group.
    $body = $content_items[0]->findAll('css', '.ecl-editor');
    $this->assertCount(1, $body);
    $this->assertEquals('My body text', $body[0]->getText());

    // Assert Organisation's contacts.
    $this->assertContactEntityDefaultDisplay($node, 'oe_organisation_contact');

    // Assert navigation part.
    $inpage_nav_expected_values['list'][] = ['label' => 'Contact', 'href' => '#contact'];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Assert header of the second field group.
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);
    $this->assertContentHeader($content_items[1], 'Contact', 'contact');
  }

}
