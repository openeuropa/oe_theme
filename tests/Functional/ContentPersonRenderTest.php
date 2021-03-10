<?php

declare(strict_types = 1);

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
 */
class ContentPersonRenderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'block',
    'system',
    'path',
    'field_group',
    'oe_theme_helper',
    'oe_theme_content_person',
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
    $page_header = $this->assertSession()->elementExists('css', '.ecl-page-header-core');
    $assert = new PatternPageHeaderAssert();
    $page_header_expected_values = [
      'title' => 'Mick Jagger',
    ];
    $assert->assertPattern($page_header_expected_values, $page_header->getOuterHtml());

    // Assert content.
    $portrait = $this->assertSession()->elementExists('css', 'article .ecl-col-lg-3 img.ecl-media-container__media');
    $this->assertContains('/themes/custom/oe_theme/images/user_icon.svg', $portrait->getAttribute('src'));
    $this->assertEmpty($portrait->getAttribute('alt'));
    $this->assertSession()->pageTextNotContains('Page contents');
    $content = $this->assertSession()->elementExists('css', 'article .ecl-col-lg-9');
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
    $this->assertContains('/files/styles/oe_theme_medium_no_crop/public/placeholder_portrait.png', $portrait->getAttribute('src'));
    $this->assertEquals('Alternative text portrait', $portrait->getAttribute('alt'));

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
          'body' => 'Audit Board of the European Communities | Arab Common Market',
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
    $this->assertContactEntityDefaultDisplay($content_items[1], 'direct_contact');

    $expandable_button = $content_items[1]->find('css', '.ecl-expandable button.ecl-button.ecl-button--secondary.ecl-expandable__toggle');
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
    $this->assertContactEntityDefaultDisplay($contacts_content[0], 'direct_contact');
    $this->assertEmpty($contacts_content[1]->getAttribute('class'));
    $this->assertContactEntityDefaultDisplay($contacts_content[1], 'organisation_reference_contact');

    // Assert Jobs field.
    $job_1 = $this->createPersonJobEntity('job_1', [
      'oe_acting' => TRUE,
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/role/MEMBER',
    ]);
    $node->set('oe_person_jobs', $job_1)->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Responsibilities',
      'href' => '#responsibilities',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(3, $content_items);
    $this->assertContentHeader($content_items[2], 'Responsibilities', 'responsibilities');
    $job_role_content = $content_items[2]->find('css', 'h3.ecl-u-type-heading-3.ecl-u-mt-none.ecl-u-mb-s');
    $this->assertEquals('(Acting) Member', $job_role_content->getText());
    $job_description_content = $content_items[2]->find('css', 'div.ecl-u-mb-l.ecl-editor');
    $this->assertEquals('Description job_1', $job_description_content->getText());

    // Assert Jobs field with multiple values.
    $job_2 = $this->createPersonJobEntity('job_2', ['oe_role_reference' => 'http://publications.europa.eu/resource/authority/role/ADVOC']);
    $node->set('oe_person_jobs', [$job_1, $job_2])->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $job_role_items = $content_items[2]->findAll('css', 'h3.ecl-u-type-heading-3.ecl-u-mt-none.ecl-u-mb-s');
    $this->assertEquals('Advocate', $job_role_items[1]->getText());
    $job_description_items = $content_items[2]->findAll('css', 'div.ecl-u-mb-l.ecl-editor');
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

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Media',
      'href' => '#media',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(4, $content_items);
    $this->assertContentHeader($content_items[3], 'Media', 'media');

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
    $social_media_content = $content_items[3]->find('css', '.ecl-social-media-follow');
    $social_links_assert->assertPattern($social_links_expected_values, $social_media_content->getOuterHtml());
    $social_links_assert->assertVariant('horizontal', $social_media_content->getOuterHtml());

    // Assert Transparency introduction field.
    $node->set('oe_person_transparency_intro', 'Transparency introduction text')->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Transparency',
      'href' => '#transparency',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(5, $content_items);
    $this->assertContentHeader($content_items[4], 'Transparency', 'transparency');
    $transparancy_intro_content = $content_items[4]->find('css', 'div.ecl-editor.ecl-u-mb-m');
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
    $transparency_links_items = $content_items[4]->findAll('css', 'div.ecl-u-pt-l.ecl-u-pb-m.ecl-u-border-bottom.ecl-u-border-color-grey-15 a');
    $this->assertCount(2, $transparency_links_items);
    $this->assertEquals('http://example.com/link_1', $transparency_links_items[0]->getAttribute('href'));
    $this->assertEquals('Person link 1', $transparency_links_items[0]->getText());
    $this->assertEquals('http://example.com/link_2', $transparency_links_items[1]->getAttribute('href'));
    $this->assertEquals('http://example.com/link_2', $transparency_links_items[1]->getText());

    // Assert Biography introduction field.
    $node->set('oe_person_biography_intro', 'Biography introduction text')->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Biography',
      'href' => '#biography',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(6, $content_items);
    $this->assertContentHeader($content_items[5], 'Biography', 'biography');
    $biography_content = $content_items[5]->find('css', 'div.ecl-editor.ecl-u-mb-m');
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
      ], [
        'label' => 'Timeline label 3',
        'body' => 'Timeline body 3',
      ], [
        'title' => 'Timeline title 4',
        'body' => 'Timeline body 4',
      ], [
        'title' => 'Timeline title 5',
      ], [
        'body' => 'Timeline body 6',
      ],
    ])->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $biography_items = $content_items[5]->findAll('css', 'ol.ecl-timeline2 li.ecl-timeline2__item');
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
    $this->assertMediaDocumentDefaultRender($content_items[5], 'cv_upload');

    // Assert Declaration of interests introduction field.
    $node->set('oe_person_interests_intro', 'Declaration of interests introduction text')->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertEquals('Declaration of interests', $content_items[5]->find('css', 'h3.ecl-u-type-heading-3')->getText());
    $this->assertEquals('Declaration of interests introduction text', $content_items[5]->find('css', 'div.ecl-u-mb-l.ecl-editor')->getText());

    // Assert Declaration of interests file field.
    $cv_media_document = $this->createMediaDocument('declaration');
    $node->set('oe_person_interests_file', $cv_media_document)->save();
    $this->drupalGet($node->toUrl());

    $content_items = $content->findAll('xpath', '/div');
    $declaration_items = $content_items[5]->findAll('xpath', '/div');
    $this->assertMediaDocumentDefaultRender($declaration_items[2], 'declaration');

    // Assert Articles and publications field.
    $document_reference = $this->createDocumentDocumentReferenceEntity('document_reference');
    $publication_reference = $this->createPublicationDocumentReferenceEntity('publication_reference');
    $node->set('oe_person_documents', [$document_reference, $publication_reference])->save();
    $this->drupalGet($node->toUrl());

    $inpage_nav_expected_values['list'][] = [
      'label' => 'Articles and presentations',
      'href' => '#articles-and-presentations',
    ];
    $inpage_nav_assert->assertPattern($inpage_nav_expected_values, $navigation->getOuterHtml());

    $content_items = $content->findAll('xpath', '/div');
    $this->assertCount(7, $content_items);
    $this->assertMediaDocumentDefaultRender($content_items[6], 'document_reference');
    $publication_teaser_content = $content_items[6]->find('css', 'div.ecl-u-border-bottom.ecl-u-border-color-grey-15 article.ecl-content-item.ecl-u-d-sm-flex.ecl-u-pb-m');
    $publication_teaser_assert = new ListItemAssert();
    $publication_teaser_expected_values = [
      'title' => 'publication_reference',
      'meta' => "Abstract | 15 April 2020\n | Associated African States and Madagascar",
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
      ['label' => 'Contact', 'href' => '#contact'],
      ['label' => 'Responsibilities', 'href' => '#responsibilities'],
      ['label' => 'Articles and presentations', 'href' => '#articles-and-presentations'],
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
    $job_description_items = $content_items[2]->findAll('css', 'div.ecl-u-mb-l.ecl-editor');
    $this->assertEquals('Description job_1', $job_description_items[0]->getText());
    $this->assertEquals('Description job_2', $job_description_items[1]->getText());
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
      'type' => 'default',
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
    $this->assertEquals($label, $element->find('css', '.ecl-timeline2__label')->getText());
    $this->assertEquals($title, $element->find('css', '.ecl-timeline2__title')->getText());
    $this->assertEquals($body, $element->find('css', '.ecl-timeline2__content')->getText());
  }

}
