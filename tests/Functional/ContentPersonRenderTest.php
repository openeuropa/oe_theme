<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\Contact;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\oe_content_person\Entity\PersonJob;
use Drupal\oe_content_person\Entity\PersonJobInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\InPageNavigationAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternPageHeaderAssert;
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

    // Assert Contact field with General Contact entity.
    $general_contact = $this->createContactEntity('direct_contact', 'oe_general');
    $node->set('oe_person_contacts', $general_contact)->save();
    $this->drupalGet($node->toUrl());
    $navigation = $this->assertSession()->elementExists('css', 'nav.ecl-inpage-navigation');
    $assert = new InPageNavigationAssert();
    $expected_values = [
      'title' => 'Page contents',
      'list' => [
        ['label' => 'Contact', 'href' => '#contact'],
      ],
    ];
    $assert->assertPattern($expected_values, $navigation->getOuterHtml());

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
   *
   * @return \Drupal\oe_content_entity_contact\Entity\ContactInterface
   *   Contact entity.
   */
  protected function createContactOrganisationReferenceEntity(string $name): ContactInterface {
    $organisation_node = $this->createOrganisationNode($name);

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
   *
   * @return \Drupal\node\NodeInterface
   *   Node entity.
   */
  protected function createOrganisationNode(string $name): NodeInterface {
    $contact = $this->createContactEntity($name . '_contact', 'oe_general');

    $node = Node::create([
      'type' => 'oe_organisation',
      'title' => "Organisation node $name",
      'oe_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => 'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'oe_organisation_contact' => $contact,
      'status' => 1,
    ]);
    $node->save();

    return $node;
  }

}
