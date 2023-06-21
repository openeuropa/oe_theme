<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_person\Entity\PersonJob;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests organisation (oe_organisation) content type render.
 *
 * @group batch1
 */
class ContentOrganisationRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'system',
    'path',
    'node',
    'address',
    'oe_theme_helper',
    'oe_theme_content_entity_contact',
    'oe_theme_content_organisation',
    'oe_theme_content_person',
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
    $file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $file->setPermanent();
    $file->save();

    $media = Media::create([
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

    $node = Node::create([
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
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header.ecl-page-header--negative');
    $assert = new PatternPageHeaderAssert();
    $expected_values = [
      'title' => 'My node title',
      'description' => 'My introduction',
      'meta' => [
        'International organisation',
        'My acronym',
      ],
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    // Change organisation type to non eu.
    $node->set('oe_organisation_org_type', 'non_eu');
    $node->set('oe_organisation_non_eu_org_type', 'http://data.europa.eu/uxp/5432');
    $node->save();
    $this->drupalGet($node->toUrl());

    $expected_values['meta'] = [
      'embassy',
      'My acronym',
    ];
    $assert->assertPattern($expected_values, $page_header->getOuterHtml());

    $logo = $this->assertSession()->elementExists('css', '.ecl-col-l-3 img.ecl-media-container__media');
    $this->assertStringContainsString('styles/oe_theme_medium_no_crop/public/example_1.jpeg', $logo->getAttribute('src'));
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

    $logo = $this->assertSession()->elementExists('css', '.ecl-col-l-3 img.ecl-media-container__media');
    $this->assertStringContainsString('files/styles/oe_theme_medium_no_crop/public/media_avportal_thumbnails/' . $file->getFilename(), $logo->getAttribute('src'));

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
    $content = $this->assertSession()->elementExists('css', '.ecl-col-l-9', $wrapper);
    $this->assertSession()->elementsCount('css', '.ecl-col-l-9', 1);
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
    $body = $content_items[1]->findAll('css', '.ecl');
    $this->assertCount(1, $body);
    $this->assertEquals('My body text', $body[0]->getText());

    // Assert Organisation's contact is displayed expanded.
    $contact_headers = $content_items[2]->findAll('css', 'h2');
    // Assert header of the third field group.
    $this->assertEquals('Contact', $contact_headers[0]->getText());
    $this->assertSession()->pageTextNotContains('Show contact details');
    $this->assertContactDefaultRender($content_items[2], 'first_general_contact');

    // Create another contact and add it to the node.
    $second_general_contact = $this->createContactEntity('second_general_contact', 'oe_general');
    $node->set('oe_organisation_contact', [
      $first_general_contact,
      $second_general_contact,
    ]);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert rendering is updated.
    $this->assertSession()->pageTextContains('Show contact details');
    $contacts = $content->findAll('css', 'div#-content.ecl-expandable__content div.ecl-row.ecl-u-mv-xl');
    $this->assertCount(2, $contacts);
    $this->assertContactDefaultRender($contacts[0], 'first_general_contact');
    $this->assertContactDefaultRender($contacts[1], 'second_general_contact');

    // Set value for only the staff search link field and assert rendering is
    // updated.
    $node->set('oe_organisation_staff_link', [
      'uri' => 'https://example.com',
      'title' => 'Search for staff',
    ])->save();
    $this->drupalGet($node->toUrl());

    // Assert Contact group was moved on the 4th position.
    $content_items = $content->findAll('xpath', '/div');
    $contact_headers = $content_items[3]->findAll('css', 'h2');
    $this->assertEquals('Contact', $contact_headers[0]->getText());
    // Assert Leadership and organisation region is rendered.
    $this->assertContentHeader($content_items[2], 'Leadership and organisation', 'leadership-and-organisation');
    // Assert staff search link values.
    $staff_search_link = $content_items[2]->findAll('css', 'a.ecl-link.ecl-link--standalone.ecl-link--icon.ecl-link--icon-after');
    $this->assertCount(1, $staff_search_link);
    $this->assertEquals('Search for staff', $staff_search_link[0]->find('css', '.ecl-link__label')->getText());
    $staff_search_link[0]->hasLink('https://example.com');

    // Create jobs for person entity.
    $person_job_1 = PersonJob::create([
      'type' => 'oe_default',
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS',
    ]);
    $person_job_1->save();
    $person_job_2 = PersonJob::create([
      'type' => 'oe_default',
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS_CHIEF',
    ]);
    $person_job_2->save();
    // Create a person node to be referenced by the organisation node.
    $person = Node::create([
      'type' => 'oe_person',
      'oe_person_first_name' => 'Jane',
      'oe_person_last_name' => 'Doe',
      'status' => 1,
    ]);
    $person->save();
    // Create document to be referenced as organisation chart.
    $chart = $this->createMediaDocument('chart');

    // Update the node values for person and organisation chart fields.
    $node->set('oe_organisation_persons', $person)
      ->set('oe_organisation_chart', $chart);
    $node->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $person_content = $content_items[2]->findAll('css', 'article.ecl-u-d-flex.ecl-u-pv-m.ecl-u-border-bottom.ecl-u-border-color-grey-15');
    $this->assertCount(1, $person_content);
    $this->assertStringContainsString('node/2', $person_content[0]->find('css', 'a.ecl-link.ecl-link--standalone')->getAttribute('href'));
    // Assert person content.
    $first_person_image = $person_content[0]->find('css', '.ecl-u-flex-shrink-0.ecl-u-mr-s.ecl-u-media-a-s.ecl-u-media-bg-size-contain.ecl-u-media-bg-repeat-none');
    // Assert default image.
    $this->assertEquals('background-image:url(/build/themes/custom/oe_theme/images/user_icon.svg)', $first_person_image->getAttribute('style'));
    // Assert role div is not printed when there are no jobs.
    $this->assertCount(0, $person_content[0]->findAll('css', '.ecl-content-item__meta.ecl-u-type-s.ecl-u-type-color-grey-75.ecl-u-mb-xs'));
    // Assert name.
    $this->assertEquals('Jane Doe', $person_content[0]->find('css', 'a.ecl-link.ecl-link--standalone')->getText());
    // Assert organisation chart document.
    $chart_document = $content_items[2]->findAll('css', '.ecl-u-mb-l.ecl-u-mt-l');
    $this->assertMediaDocumentDefaultRender($chart_document[0], 'chart', 'English', '2.96 KB - PDF', '', 'Download');

    // Update person node with jobs and assert rendering is updated.
    $person->set('oe_person_jobs', [$person_job_1, $person_job_2]);
    $person->save();
    $this->getSession()->reload();
    $person_content = $content_items[2]->findAll('css', 'article.ecl-u-d-flex.ecl-u-pv-m.ecl-u-border-bottom.ecl-u-border-color-grey-15');
    $this->assertEquals('Adviser, Chief Adviser', $person_content[0]->find('css', '.ecl-content-item__meta.ecl-u-type-s.ecl-u-type-color-grey-75.ecl-u-mb-xs')->getText());
  }

}
