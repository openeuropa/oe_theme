<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\Contact;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\oe_content_person\Entity\PersonJob;
use Drupal\oe_content_person\Entity\PersonJobInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
use Drupal\Tests\oe_theme\PatternAssertions\SocialMediaLinksAssert;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Test Person content type rendering.
 *
 * @group batch2
 */
class ContentPersonRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_theme_content_person',
    'oe_multilingual',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Give anonymous users permission to view entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view published skos concept entities')
      ->grantPermission('view published oe_contact')
      ->save();
  }

  /**
   * Tests Consultation full view mode rendering.
   */
  public function testPersonRendering(): void {
    // Make person node and person job translatable.
    \Drupal::service('content_translation.manager')->setEnabled('node', 'oe_person', TRUE);
    \Drupal::service('content_translation.manager')->setEnabled('oe_person_job', 'oe_default', TRUE);
    // Make person last name and job description fields translatable.
    $field_config = $this->getStorage('field_config')->load('oe_person_job.oe_default.oe_description');
    $field_config->setTranslatable(TRUE);
    $field_config->save();
    $field_config = $this->getStorage('field_config')->load('node.oe_person.oe_summary');
    $field_config->setTranslatable(TRUE);
    $field_config->save();
    \Drupal::service('router.builder')->rebuild();
    // Create a Person node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $values = [
      'type' => 'oe_person',
      'oe_subject' => 'http://data.europa.eu/uxp/1005',
      'oe_person_type' => 'eu',
      'oe_person_first_name' => 'Mick',
      'oe_person_last_name' => 'Jagger',
      'oe_person_gender' => 'http://publications.europa.eu/resource/authority/human-sex/MALE',
      'oe_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'uid' => 0,
      'status' => 1,
    ];
    $node = Node::create($values);
    $node->save();
    $this->drupalGet($node->toUrl());

    // Assert page header - metadata.
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header');
    $assert = new PatternPageHeaderAssert();
    $page_header_expected_values = [
      'meta' => [],
      'title' => 'Mick Jagger',
    ];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Assert content.
    $portrait = $this->assertSession()->elementExists('css', 'article .ecl-col-l-3 img.ecl-media-container__media');
    $this->assertStringContainsString('/themes/custom/oe_theme/images/user_icon.svg', $portrait->getAttribute('src'));
    $this->assertEmpty($portrait->getAttribute('alt'));
    $this->assertSession()->pageTextNotContains('Page contents');
    $content = $this->assertSession()->elementExists('css', 'article .ecl-col-l-9');
    $this->assertEmpty($content->getText());

    // Assert Display name field.
    $node->set('oe_person_displayed_name', 'Jagger Mick')->save();
    $this->drupalGet($node->toUrl());
    $page_header_expected_values = [
      'title' => 'Jagger Mick',
    ];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Assert Introduction field.
    $node->set('oe_summary', 'Person introduction')->save();
    $this->drupalGet($node->toUrl());
    $page_header_expected_values['description'] = 'Person introduction';
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Assert Portrait photo field.
    $portrait_media = $this->createMediaImage('portrait');
    $node->set('oe_person_photo', $portrait_media)->save();
    $this->drupalGet($node->toUrl());
    $this->assertStringContainsString('/files/styles/oe_theme_medium_no_crop/public/placeholder_portrait.png', $portrait->getAttribute('src'));
    $this->assertEquals('Alternative text portrait', $portrait->getAttribute('alt'));

    // Unpublish the media and assert it is not rendered anymore.
    $portrait_media->set('status', 0);
    $portrait_media->save();
    $this->drupalGet($node->toUrl());
    $this->assertStringNotContainsString('/files/styles/oe_theme_medium_no_crop/public/placeholder_portrait.png', $portrait->getAttribute('src'));

    // Publish the media.
    $portrait_media->set('status', 1);
    $portrait_media->save();

    // Assert Department field with single value.
    $node->set('oe_departments', 'http://publications.europa.eu/resource/authority/corporate-body/ABEC')->save();
    $this->drupalGet($node->toUrl());
    $this->assertSession()->pageTextNotContains('Page contents');
    $department_content = $content->find('css', '.ecl-description-list');
    $department_content_html = $department_content->getOuterHtml();
    $field_list_assert = new FieldListAssert();
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'Department',
          'body' => 'Audit Board of the European Communities',
        ],
      ],
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $department_content_html);
    $field_list_assert->assertVariant('horizontal', $department_content_html);

    // Assert Department field with multiple values.
    $node->set('oe_departments', [
      'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'http://publications.europa.eu/resource/authority/corporate-body/ACM',
    ])->save();
    $this->drupalGet($node->toUrl());
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'Departments',
          'body' => 'Audit Board of the European Communities, Arab Common Market',
        ],
      ],
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $department_content->getOuterHtml());

    // Assert Contact field with Organisation reference Contact entity with
    // Organisation without Contact.
    $organisation_reference_empty_contact = $this->createContactOrganisationReferenceEntity('organisation_reference', FALSE);
    $node->set('oe_person_contacts', [
      $organisation_reference_empty_contact,
    ])->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertEquals(1, count($content_items));
    $this->assertSession()->pageTextNotContains('Page contents');

    // Assert Contact field with General Contact entity.
    $general_contact = $this->createContactEntity('direct_contact', 'oe_general');
    $node->set('oe_person_contacts', [
      $organisation_reference_empty_contact,
      $general_contact,
    ])->save();
    $this->drupalGet($node->toUrl());

    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $inpage_nav_assert = new InPageNavigationAssert();
    $inpage_nav_expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Contact', 'href' => '#contact'],
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);
    $this->assertContentHeader($content_items[1], 'Contact', 'contact');
    $this->assertContactDefaultRender($content_items[1], 'direct_contact');

    $expandable_button = $content_items[1]->find('css', '.ecl-expandable button.ecl-button.ecl-button--ghost.ecl-expandable__toggle');
    $this->assertEquals('Show contact details', $expandable_button->getAttribute('data-ecl-label-collapsed'));
    $this->assertEquals('Hide contact details', $expandable_button->getAttribute('data-ecl-label-expanded'));

    // Assert Contact field with Organisation reference Contact entity.
    $organisation_reference_contact = $this->createContactOrganisationReferenceEntity('organisation_reference');
    $node->set('oe_person_contacts', [
      $organisation_reference_empty_contact,
      $general_contact,
      $organisation_reference_contact,
    ])->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $contacts_content = $content_items[1]->findAll('xpath', '//div[@class="ecl-expandable__content"]/div');
    $this->assertEquals(2, count($contacts_content));
    $this->assertEquals('ecl-row ecl-u-mv-xl', $contacts_content[0]->getAttribute('class'));
    $this->assertContactDefaultRender($contacts_content[0], 'direct_contact');
    $this->assertEmpty($contacts_content[1]->getAttribute('class'));
    $this->assertContactDefaultRender($contacts_content[1], 'organisation_reference_contact');

    // Assert Jobs field.
    $job_1 = $this->createPersonJobEntity('job_1', [
      'oe_acting' => TRUE,
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS',
    ]);
    $node->set('oe_person_jobs', $job_1)->save();
    $this->drupalGet($node->toUrl());

    $page_header_expected_values['meta'] = ['(Acting) Adviser'];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Responsibilities',
      'href' => '#responsibilities',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    // Singe Person Job with description should be shown without label.
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);
    $this->assertContentHeader($content_items[2], 'Responsibilities', 'responsibilities');
    $job_role_content = $content_items[2]->find('css', 'h3.ecl-u-type-heading-3.ecl-u-mt-none.ecl-u-mb-s');
    $this->assertNull($job_role_content);
    $job_description_content = $content_items[2]->find('css', 'div.ecl');
    $this->assertEquals('Description job_1', $job_description_content->getText());

    // Add person and job translation and assert the job label is not shown.
    $job_1->addTranslation('bg', ['oe_description' => 'Description bg'])->save();
    $node->addTranslation('bg', ['oe_summary' => 'Person introduction bg'])->save();
    $this->drupalGet('/bg/node/' . $node->id(), ['external' => FALSE]);
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);
    $this->assertContentHeader($content_items[2], 'Responsibilities', 'responsibilities');
    $job_role_content = $content_items[2]->find('css', 'h3.ecl-u-type-heading-3.ecl-u-mt-none.ecl-u-mb-s');
    $this->assertNull($job_role_content);
    $job_description_content = $content_items[2]->find('css', 'div.ecl');
    $this->assertEquals('Description bg', $job_description_content->getText());

    // Singe Person Job without description should not be shown
    // and Responsibilities section is hidden.
    $job_1->set('oe_description', NULL);
    $job_1->save();
    $this->drupalGet($node->toUrl());

    $page_header_expected_values['meta'] = ['(Acting) Adviser'];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());
    array_pop($inpage_nav_expected_values['list']);
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);
    $this->assertNull($content->findById('responsibilities'));
    $this->assertStringNotContainsString('Responsibilities', $content->getOuterHtml());
    $this->assertStringNotContainsString('(Acting) Adviser', $content->getOuterHtml());
    $this->assertStringNotContainsString('Description job_1', $content->getOuterHtml());

    // Assert Jobs field with multiple values.
    $job_2 = $this->createPersonJobEntity('job_2', ['oe_role_reference' => 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS_CHIEF']);
    $node->set('oe_person_jobs', [$job_1, $job_2])->save();
    $this->drupalGet($node->toUrl());

    $page_header_expected_values['meta'] = ['(Acting) Adviser, Chief Adviser'];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());
    $inpage_nav_expected_values['list'][] = [
      'label' => 'Responsibilities',
      'href' => '#responsibilities',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $job_role_items = $content_items[2]->findAll('css', 'h3.ecl-u-type-heading-3.ecl-u-mt-none.ecl-u-mb-s');
    $this->assertEquals('Chief Adviser', $job_role_items[0]->getText());
    $job_description_items = $content_items[2]->findAll('css', 'div.ecl-u-mb-l.ecl');
    $this->assertEquals('Description job_2', $job_description_items[0]->getText());

    // Both Person Job without description should not be shown
    // and Responsibilities section is hidden.
    $job_2->set('oe_description', NULL);
    $job_2->save();
    $this->drupalGet($node->toUrl());

    $page_header_expected_values['meta'] = ['(Acting) Adviser, Chief Adviser'];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());
    array_pop($inpage_nav_expected_values['list']);
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(2, $content_items);
    $this->assertNull($content->findById('responsibilities'));
    $this->assertStringNotContainsString('Responsibilities', $content->getOuterHtml());
    $this->assertStringNotContainsString('(Acting) Adviser', $content->getOuterHtml());
    $this->assertStringNotContainsString('Description job_1', $content->getOuterHtml());
    $this->assertStringNotContainsString('Chief Adviser', $content->getOuterHtml());
    $this->assertStringNotContainsString('Description job_2', $content->getOuterHtml());

    // Both Person Job with description should be shown
    // and Responsibilities section is visible.
    $job_1->set('oe_description', 'Description job_1');
    $job_1->save();
    $job_2->set('oe_description', 'Description job_2');
    $job_2->save();
    $this->drupalGet($node->toUrl());

    $page_header_expected_values['meta'] = ['(Acting) Adviser, Chief Adviser'];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());
    $inpage_nav_expected_values['list'][] = [
      'label' => 'Responsibilities',
      'href' => '#responsibilities',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $job_role_items = $content_items[2]->findAll('css', 'h3.ecl-u-type-heading-3.ecl-u-mt-none.ecl-u-mb-s');
    $this->assertEquals('(Acting) Adviser', $job_role_items[0]->getText());
    $this->assertEquals('Chief Adviser', $job_role_items[1]->getText());
    $job_description_items = $content_items[2]->findAll('css', 'div.ecl-u-mb-l.ecl');
    $this->assertEquals('Description job_1', $job_description_items[0]->getText());
    $this->assertEquals('Description job_2', $job_description_items[1]->getText());

    // Assert Social media links field.
    $node->set('oe_social_media_links', [
      [
        'uri' => 'https://fb.com/person',
        'title' => 'Person Facebook',
        'link_type' => 'facebook',
      ], [
        'uri' => 'https://linkedin.com/person',
        'title' => 'Person LinkedIn',
        'link_type' => 'linkedin',
      ],
    ])->save();
    $this->drupalGet($node->toUrl());

    $social_links_assert = new SocialMediaLinksAssert();
    $social_links_expected_values = [
      'title' => 'Follow the latest progress and learn more about getting involved.',
      'links' => [
        [
          'service' => 'facebook',
          'label' => 'Person Facebook',
          'url' => 'https://fb.com/person',
        ], [
          'service' => 'linkedin',
          'label' => 'Person LinkedIn',
          'url' => 'https://linkedin.com/person',
        ],
      ],
    ];
    $social_media_content = $content->find('css', '.ecl-social-media-follow');
    $social_links_assert->assertPattern($social_links_expected_values, $social_media_content->getOuterHtml());
    $social_links_assert->assertVariant('horizontal', $social_media_content->getOuterHtml());

    // Create some medias to reference in the media field.
    $node->set('oe_person_media', [
      $this->createMediaImage('first_media')->id(),
      $this->createMediaImage('second_media')->id(),
    ])->save();
    $this->drupalGet($node->toUrl());
    $inpage_nav_expected_values['list'][] = [
      'label' => 'Media',
      'href' => '#media',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(5, $content_items);
    $this->assertContentHeader($content_items[3], 'Media', 'media');

    // Assert the rendering of the media field as gallery.
    // @todo Implement the gallery pattern assertion class.
    $gallery = $this->assertSession()->elementExists('css', 'section.ecl-gallery');
    $items = $gallery->findAll('css', 'li.ecl-gallery__item');
    $this->assertCount(2, $items);

    // Test the contents of the first item. The second item would have a similar
    // structure so no need to test.
    $first_item = $items[0]->find('css', '.ecl-gallery__thumbnail img');
    $this->assertEquals('Alternative text first_media', $first_item->getAttribute('alt'));
    // @todo Remove when support for core 10.2.x is dropped.
    // Core shipped image styles are converted to webp extension.
    $image_extension = version_compare(\Drupal::VERSION, '10.3', '>') ? 'png.webp' : 'png';
    $this->assertStringContainsString("/styles/large/public/placeholder_first_media.$image_extension?itok=", $first_item->getAttribute('src'));
    $caption = $items[0]->find('css', '.ecl-gallery__description');
    $this->assertStringContainsString('Test image first_media', $caption->getOuterHtml());
    $this->assertEmpty($caption->find('css', '.ecl-gallery__meta')->getText());

    // Assert Transparency introduction field.
    $node->set('oe_person_transparency_intro', 'Transparency introduction text')->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Transparency',
      'href' => '#transparency',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(6, $content_items);
    $this->assertContentHeader($content_items[5], 'Transparency', 'transparency');
    $transparancy_intro_content = $content_items[5]->find('css', 'div.ecl.ecl-u-mb-m');
    $this->assertEquals('Transparency introduction text', $transparancy_intro_content->getText());

    // Assert Transparency links field.
    $node->set('oe_person_transparency_links', [
      [
        'uri' => 'http://example.com/link_1',
        'title' => 'Person link 1',
      ], [
        'uri' => 'http://example.com/link_2',
      ],
    ])->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $transparency_links_items = $content_items[5]->findAll('css', 'div.ecl-u-pt-l.ecl-u-pb-m.ecl-u-border-bottom.ecl-u-border-color-neutral-40 a');
    $this->assertCount(2, $transparency_links_items);
    $this->assertEquals('http://example.com/link_1', $transparency_links_items[0]->getAttribute('href'));
    $this->assertEquals('Person link 1', $transparency_links_items[0]->getText());
    $first_link_icon = $transparency_links_items[0]->find('css', 'a.ecl-link svg.ecl-icon.ecl-icon--2xs.ecl-link__icon');
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $first_link_icon->getHtml());
    $this->assertEquals('http://example.com/link_2', $transparency_links_items[1]->getAttribute('href'));
    $this->assertEquals('http://example.com/link_2', $transparency_links_items[1]->getText());
    $second_link_icon = $transparency_links_items[1]->find('css', 'a.ecl-link svg.ecl-icon.ecl-icon--2xs.ecl-link__icon');
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#external" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $second_link_icon->getHtml());

    // Assert Biography introduction field.
    $node->set('oe_person_biography_intro', 'Biography introduction text')->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Biography',
      'href' => '#biography',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(7, $content_items);
    $this->assertContentHeader($content_items[6], 'Biography', 'biography');
    $biography_content = $content_items[6]->find('css', 'div.ecl.ecl-u-mb-m');
    $this->assertEquals('Biography introduction text', $biography_content->getText());

    // Assert Biography field.
    $node->set('oe_person_biography_timeline', [
      [
        'label' => 'Timeline label 1',
        'title' => 'Timeline title 1',
        'body' => 'Timeline body 1',
      ], [
        'label' => 'Timeline label 2',
        'title' => 'Timeline title 2',
        'body' => '',
      ], [
        'label' => 'Timeline label 3',
        'title' => '',
        'body' => 'Timeline body 3',
      ], [
        'label' => '',
        'title' => 'Timeline title 4',
        'body' => 'Timeline body 4',
      ], [
        'label' => '',
        'title' => 'Timeline title 5',
        'body' => '',
      ], [
        'label' => '',
        'title' => '',
        'body' => 'Timeline body 6',
      ],
    ])->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $biography_items = $content_items[6]->findAll('css', 'ol.ecl-timeline li.ecl-timeline__item');
    $this->assertCount(6, $biography_items);
    $this->assertTimelineItem($biography_items[0], 'Timeline label 1', 'Timeline title 1', 'Timeline body 1');
    $this->assertTimelineItem($biography_items[1], 'Timeline label 2', 'Timeline title 2', '');
    $this->assertTimelineItem($biography_items[2], 'Timeline label 3', '', 'Timeline body 3');
    $this->assertTimelineItem($biography_items[3], '', 'Timeline title 4', 'Timeline body 4');
    $this->assertTimelineItem($biography_items[4], '', 'Timeline title 5', '');
    $this->assertTimelineItem($biography_items[5], '', '', 'Timeline body 6');

    // Assert CV upload field.
    $cv_media_document = $this->createMediaDocument('cv_upload');
    $node->set('oe_person_cv', $cv_media_document)->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertMediaDocumentDefaultRender($content_items[6], 'cv_upload', 'English', '2.96 KB - PDF', "sample_cv_upload.pdf", 'Download');

    // Assert Declaration of interests introduction field.
    $node->set('oe_person_interests_intro', 'Declaration of interests introduction text')->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertEquals('Declaration of interests', $content_items[6]->find('css', 'h3.ecl-u-type-heading-3')->getText());
    $this->assertEquals('Declaration of interests introduction text', $content_items[6]->find('css', 'div.ecl-u-mb-l.ecl')->getText());

    // Assert Declaration of interests file field.
    $cv_media_document = $this->createMediaDocument('declaration');
    $node->set('oe_person_interests_file', $cv_media_document)->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $declaration_items = $content_items[6]->findAll('xpath', '/div');
    $this->assertMediaDocumentDefaultRender($declaration_items[2], 'declaration', 'English', '2.96 KB - PDF', "sample_declaration.pdf", 'Download');

    // Assert Articles and publications field.
    $document_reference = $this->createDocumentDocumentReferenceEntity('document_reference');
    $publication_reference = $this->createPublicationDocumentReferenceEntity('publication_reference');
    $node->set('oe_person_documents', [
      $document_reference,
      $publication_reference,
    ])->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Articles and presentations',
      'href' => '#articles-and-presentations',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(8, $content_items);
    $this->assertMediaDocumentDefaultRender($content_items[7], 'document_reference', 'English', '2.96 KB - PDF', "sample_document_reference.pdf", 'Download');
    $publication_teaser_content = $content_items[7]->find('css', 'div.ecl-u-border-bottom.ecl-u-border-color-neutral-40');
    $publication_teaser_assert = new ListItemAssert();
    $publication_teaser_expected_values = [
      'title' => 'publication_reference',
      'meta' => [
        'Abstract',
        '15 April 2020',
        'Associated African States and Madagascar',
      ],
      'description' => 'Teaser text',
    ];
    $publication_teaser_assert->assertPattern($publication_teaser_expected_values, $publication_teaser_content->getOuterHtml());

    // Assert non-eu person.
    $job_1->set('oe_role_name', 'Singer');
    $job_1->set('oe_role_reference', NULL);
    $job_1->set('oe_acting', NULL)->save();
    $job_2->set('oe_role_reference', NULL);
    $job_2->set('oe_role_name', 'Dancer')->save();
    $node->set('oe_person_type', 'non_eu');
    $organisation_node = $this->createOrganisationNode('non_eu');
    $node->set('oe_person_organisation', $organisation_node)->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'] = [
      [
        'label' => 'Contact',
        'href' => '#contact',
      ],
      [
        'label' => 'Responsibilities',
        'href' => '#responsibilities',
      ],
      [
        'label' => 'Articles and presentations',
        'href' => '#articles-and-presentations',
      ],
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(4, $content_items);
    $this->assertContentHeader($content_items[1], 'Contact', 'contact');
    $this->assertContentHeader($content_items[2], 'Responsibilities', 'responsibilities');
    $this->assertContentHeader($content_items[3], 'Articles and presentations', 'articles-and-presentations');

    $organisation_content = $content_items[0]->find('css', '.ecl-description-list');
    $organisation_content_html = $organisation_content->getOuterHtml();
    $field_list_assert = new FieldListAssert();
    $field_list_expected_values = [
      'items' => [
        [
          'label' => 'Organisation',
          'body' => 'Organisation node non_eu',
        ],
      ],
    ];
    $field_list_assert->assertPattern($field_list_expected_values, $organisation_content_html);
    $field_list_assert->assertVariant('horizontal', $organisation_content_html);

    $job_role_items = $content_items[2]->findAll('css', 'h3.ecl-u-type-heading-3.ecl-u-mt-none.ecl-u-mb-s');
    $this->assertEquals('Singer', $job_role_items[0]->getText());
    $this->assertEquals('Dancer', $job_role_items[1]->getText());
    $job_description_items = $content_items[2]->findAll('css', 'div.ecl-u-mb-l.ecl');
    $this->assertEquals('Description job_1', $job_description_items[0]->getText());
    $this->assertEquals('Description job_2', $job_description_items[1]->getText());

    // The gallery is not rendered on non_eu persons.
    $this->assertSession()->elementNotExists('css', 'section.ecl-gallery');

    // Assert Description field.
    $node->set('oe_person_description', 'Person description text')->save();
    $this->drupalGet($node->toUrl());
    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(5, $content_items);
    $this->assertEquals('Person description text', $content_items[3]->find('css', '.ecl p')->getText());
  }

  /**
   * Creates Person job entity.
   *
   * @param string $name
   *   String to be used in test data.
   * @param array $values
   *   Entity values.
   *
   * @return \Drupal\oe_content_person\Entity\PersonJobInterface
   *   Person job entity
   */
  protected function createPersonJobEntity(string $name, array $values): PersonJobInterface {
    $values = [
      'type' => 'oe_default',
      'oe_description' => "Description $name",
    ] + $values;
    $person_job = PersonJob::create($values);
    $person_job->save();

    return $person_job;
  }

  /**
   * Creates Contact entity Organisation reference bundle.
   *
   * @param string $name
   *   Name of the entity.
   * @param bool $create_organisation_contact
   *   TRUE if create Organisation node with optional Contact entity.
   *
   * @return \Drupal\oe_content_entity_contact\Entity\ContactInterface
   *   Contact entity.
   */
  protected function createContactOrganisationReferenceEntity(string $name, bool $create_organisation_contact = TRUE): ContactInterface {
    $organisation_node = $this->createOrganisationNode($name, $create_organisation_contact);

    $contact = Contact::create([
      'bundle' => 'oe_organisation_reference',
      'name' => "$name contact",
      'oe_node_reference' => $organisation_node,
      'status' => CorporateEntityInterface::PUBLISHED,
    ]);
    $contact->save();

    return $contact;
  }

  /**
   * Creates Organisation node.
   *
   * @param string $name
   *   Name of the entity.
   * @param bool $create_organisation_contact
   *   TRUE if create Organisation node with optional Contact entity.
   *
   * @return \Drupal\node\NodeInterface
   *   Node entity.
   */
  protected function createOrganisationNode(string $name, bool $create_organisation_contact = TRUE): NodeInterface {
    $node = Node::create([
      'type' => 'oe_organisation',
      'title' => "Organisation node $name",
      'oe_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => 'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'status' => 1,
    ]);

    if ($create_organisation_contact) {
      $contact = $this->createContactEntity($name . '_contact', 'oe_general');
      $node->set('oe_organisation_contact', $contact);
    }

    $node->save();

    return $node;
  }

  /**
   * Asserts rendering of timeline item.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   Rendered element.
   * @param string $label
   *   Expected label.
   * @param string $title
   *   Expected title.
   * @param string $body
   *   Expected body.
   */
  protected function assertTimelineItem(NodeElement $element, string $label, string $title, string $body):void {
    $this->assertEquals($label, $element->find('css', '.ecl-timeline__label')->getText());
    $this->assertEquals($title, $element->find('css', '.ecl-timeline__title')->getText());
    $this->assertEquals($body, $element->find('css', '.ecl-timeline__content')->getText());
  }

}
