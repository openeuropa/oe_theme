<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\media\Entity\Media;

/**
 * Tests organisation (oe_organisation) content type render.
 *
 * @group batch3
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
    'media_avportal_mock',
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

    $first_general_contact = $this->createContactEntity('first_general_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);

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
    $node->set('oe_organisation_contact', [$first_general_contact]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert navigation part.
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Contact', 'href' => '#contact'],
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

    // Add overview values.
    $node->set('oe_organisation_overview', [
      [
        'term' => 'Overview Term 1',
        'description' => 'Overview Description 1',
      ],
      [
        'term' => 'Overview Term 2',
        'description' => 'Overview Description 2',
      ],
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert content part.
    $wrapper = $this->assertSession()->elementExists('css', '.ecl-row.ecl-u-mt-l');
    $content = $this->assertSession()->elementExists('css', '.ecl-col-lg-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-lg-9', 1);
    $content_items = $content->findAll('xpath', '/div');

    // Assert header of the first field group.
    $this->assertContentHeader($content_items[0], 'Overview', 'overview');

    // Assert values of the first group.
    $overview = $content_items[0]->findAll('css', 'dl.ecl-description-list.ecl-description-list--horizontal');
    $this->assertCount(1, $overview);
    $overview_terms = $overview[0]->findAll('css', 'dt.ecl-description-list__term');
    $this->assertCount(2, $overview_terms);
    $this->assertEquals('Overview Term 1', $overview_terms[0]->getText());
    $this->assertEquals('Overview Term 2', $overview_terms[1]->getText());
    $overview_descriptions = $overview[0]->findAll('css', 'dd.ecl-description-list__definition');
    $this->assertCount(2, $overview_descriptions);
    $this->assertEquals('Overview Description 1', $overview_descriptions[0]->getText());
    $this->assertEquals('Overview Description 2', $overview_descriptions[1]->getText());

    // Assert values of the second group.
    $body = $content_items[1]->findAll('css', '.ecl-editor');
    $this->assertCount(1, $body);
    $this->assertEquals('My body text', $body[0]->getText());

    // Assert Organisation's contact is displayed expanded.
    $contact_headers = $content_items[2]->findAll('css', 'h2');
    // Assert header of the third field group.
    $this->assertEquals('Contact', $contact_headers[0]->getText());
    $this->assertSession()->pageTextNotContains('Show contact details');
    $this->assertContactDefaultRender($content_items[2], 'first_general_contact');

    // Create another contact and add it to the node.
    $second_general_contact = $this->createContactEntity('second_general_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);
    $node->set('oe_organisation_contact', [$first_general_contact, $second_general_contact]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert rendering is updated.
    $this->assertSession()->pageTextContains('Show contact details');
    $contacts = $content->findAll('css', 'div#-content.ecl-expandable__content div.ecl-row.ecl-u-mv-xl');
    $this->assertCount(2, $contacts);
    $this->assertContactDefaultRender($contacts[0], 'first_general_contact');
    $this->assertContactDefaultRender($contacts[1], 'second_general_contact');
  }

}
