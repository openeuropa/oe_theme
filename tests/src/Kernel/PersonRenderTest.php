<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\Contact;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\oe_content_person\Entity\PersonJob;
use Drupal\oe_content_person\Entity\PersonJobInterface;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests consultation rendering.
 *
 * @group batch2
 */
class PersonRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'options',
    'field_group',
    'composite_reference',
    'description_list_field',
    'oe_content_departments_field',
    'oe_content_person',
    'oe_content_organisation',
    'oe_content_organisation_reference',
    'oe_content_social_media_links_field',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
    'oe_theme_content_organisation',
    'oe_theme_content_organisation_reference',
    'oe_theme_content_person',
    'description_list_field',
    'oe_content_sub_entity',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $entities = [
      'oe_contact',
      'oe_document_reference',
      'oe_person_job',
    ];
    foreach ($entities as $entity) {
      $this->installEntitySchema($entity);
    }

    $this->installConfig([
      'oe_content_departments_field',
      'oe_content_social_media_links_field',
      'oe_content_organisation_reference',
      'oe_content_organisation',
      'oe_content_person',
      'oe_theme_content_organisation',
      'oe_theme_content_person',
    ]);

    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
    module_load_include('install', 'oe_content_person');
    oe_content_person_install(FALSE);

    $this->setUpCurrentUser([], [], TRUE);
  }

  /**
   * Test a person being rendered as a teaser.
   */
  public function testTeaser(): void {
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

    // Check teaser with required fields only.
    $html = $this->getRenderedNode($node);
    $expected_values = [
      'title' => 'Mick Jagger',
      'badges' => NULL,
      'meta' => NULL,
      'image' => [
        'src' => 'user_icon.svg',
        'alt' => '',
      ],
      'lists' => NULL,
    ];
    $assert = new ListItemAssert();
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_secondary', $html);

    // Assert Display name field.
    $node->set('oe_person_displayed_name', 'Jagger Mick')->save();
    $expected_values['title'] = 'Jagger Mick';
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert Portrait photo field.
    $portrait_media = $this->createMediaImage('person_portrait');
    $node->set('oe_person_photo', $portrait_media)->save();
    $expected_values['image']['src'] = 'placeholder_person_portrait.png';
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert Departments field.
    $node->set('oe_departments', 'http://publications.europa.eu/resource/authority/corporate-body/ABEC')->save();
    // @todo Replace additional_information assertion with lists in EWPP-2508.
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert multiple values in Departments field.
    $node->set('oe_departments', [
      'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'http://publications.europa.eu/resource/authority/corporate-body/ACM',
    ])->save();
    // @todo Replace additional_information assertion with lists in EWPP-2508.
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert Contact field with Organisation reference Contact entity with
    // Organisation without Contact.
    $organisation_reference_empty_contact = $this->createContactOrganisationReferenceEntity('organisation_reference', FALSE);
    $node->set('oe_person_contacts', [
      $organisation_reference_empty_contact,
    ])->save();
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert Contacts field.
    $general_contact = $this->createContactEntity('direct_contact', 'oe_general');
    $node->set('oe_person_contacts', [
      $organisation_reference_empty_contact,
      $general_contact,
    ])->save();
    // @todo Replace additional_information assertion with lists in EWPP-2508.
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert Contacts field with an organisation as contact.
    $organisation_reference_contact = $this->createContactOrganisationReferenceEntity('organisation_reference');
    $node->set('oe_person_contacts', [
      $organisation_reference_empty_contact,
      $general_contact,
      $organisation_reference_contact,
    ])->save();

    // @todo Replace additional_information assertion with lists in EWPP-2508.
    // Assert Jobs field and highlighted label.
    $job_1 = $this->createPersonJobEntity('job_1', [
      'oe_acting' => TRUE,
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS',
    ]);
    $node->set('oe_person_contacts', NULL);
    $node->set('oe_person_jobs', $job_1);
    $node->set('sticky', NodeInterface::STICKY)->save();
    $expected_values['badges'] = [
      [
        'label' => 'Highlighted',
        'variant' => 'highlight',
      ],
    ];
    $expected_values['meta'] = [
      '(Acting) Adviser',
    ];
    // @todo Replace additional_information assertion with lists in EWPP-2508.
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    $job_2 = $this->createPersonJobEntity('job_2', ['oe_role_reference' => 'http://publications.europa.eu/resource/authority/role-qualifier/ADVIS_CHIEF']);
    $node->set('oe_person_jobs', [$job_1, $job_2])->save();
    $expected_values['meta'] = [
      '(Acting) Adviser, Chief Adviser',
    ];
    // @todo Replace additional_information assertion with lists in EWPP-2508.
    $assert->assertPattern($expected_values, $this->getRenderedNode($node));

    // Assert non-eu person.
    $job_1->set('oe_role_name', 'Singer');
    $job_1->set('oe_role_reference', NULL);
    $job_1->set('oe_acting', NULL)->save();
    $job_2->set('oe_role_reference', NULL);
    $job_2->set('oe_role_name', 'Dancer')->save();
    $node->set('oe_person_type', 'non_eu');
    $node->set('oe_person_contacts', $general_contact);
    $organisation_node = $this->createOrganisationNode('non_eu');
    $node->set('oe_person_organisation', $organisation_node)->save();

    $expected_values = [
      'title' => 'Jagger Mick',
      'badges' => [
        'label' => 'Highlighted',
        'variant' => 'highlight',
      ],
      'meta' => [
        'Singer, Dancer',
      ],
      'image' => [
        'src' => 'person_portrait.png',
        'alt' => '',
      ],
      // @todo Replace additional_information assertion with lists in EWPP-2508.
    ];
    $html = $this->getRenderedNode($node);
    $assert->assertPattern($expected_values, $html);
    // @todo Replace additional_information assertion with lists in EWPP-2508.
  }

  /**
   * Renders node using provided view mode.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node entity.
   * @param string $view_mode
   *   Node view mode.
   *
   * @return string
   *   Rendered content.
   */
  protected function getRenderedNode(NodeInterface $node, $view_mode = 'teaser'): string {
    $build = $this->nodeViewBuilder->view($node, $view_mode);
    return $this->renderRoot($build);
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

}
