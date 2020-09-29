<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use Drupal\node\Entity\Node;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\Contact;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\Tests\oe_theme\PatternAssertions\FieldListAssert;
use Drupal\Tests\oe_theme\PatternAssertions\ListItemAssert;
use Drupal\Tests\oe_theme\PatternAssertions\PatternAssertState;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;

/**
 * Tests the organisation rendering.
 */
class OrganisationRenderTest extends ContentRenderTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'address',
    'field_group',
    'entity_reference_revisions',
    'link',
    'options',
    'image',
    'inline_entity_form',
    'oe_content_featured_media_field',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_organisation',
    'composite_reference',
    'oe_theme_content_organisation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('oe_contact');
    $this->installConfig([
      'oe_content_featured_media_field',
      'oe_content_entity_contact',
      'oe_content_organisation',
    ]);

    module_load_include('install', 'oe_theme_content_organisation');
    oe_theme_content_organisation_install();

    module_load_include('install', 'oe_content');
    oe_content_install();

    // Set current user to UID 1, so that by default we can access everything.
    $account = User::load(1);
    $this->setCurrentUser($account);
  }

  /**
   * Test a organisation being rendered as a teaser.
   */
  public function testOrganisationTeaser(): void {
    $logo_media = $this->createMediaImage('organisation_logo');
    $contact = $this->createContactEntity('organisation_contact', 'oe_general', CorporateEntityInterface::PUBLISHED);

    $node = Node::create([
      'type' => 'oe_organisation',
      'title' => 'Organisation name',
      'oe_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
      'oe_teaser' => 'The teaser text',
      'oe_organisation_acronym' => 'Acronym',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => 'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'oe_organisation_logo' => [
        [
          'target_id' => $logo_media->id(),
        ],
      ],
      'oe_organisation_contact' => [
        [
          'target_id' => $contact->id(),
          'target_revision_id' => $contact->getRevisionId(),
        ],
      ],
      'status' => 1,
    ]);
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $assert = new ListItemAssert();
    $expected_values = [
      'title' => 'Organisation name',
      'url' => '/node/1',
      'description' => 'The teaser text',
      'meta' => 'International organisation | Acronym',
      'image' => [
        'src' => 'placeholder_organisation_logo.png',
        'alt' => '',
      ],
      'date' => NULL,
      'additional_information' => [
        new PatternAssertState(new FieldListAssert(), [
          'items' => [
            [
              'label' => 'Website',
              'body' => 'http://www.example.com/website_organisation_contact',
            ],
            [
              'label' => 'Email',
              'body' => 'organisation_contact@example.com',
            ],
            [
              'label' => 'Phone number',
              'body' => 'Phone number organisation_contact',
            ],
            [
              'label' => 'Address',
              'body' => 'Address organisation_contact, 1001 Brussels, Belgium',
            ],
          ],
        ]),
      ],
    ];
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_secondary', $html);

    // Change organisation type to non eu.
    $node->set('oe_organisation_org_type', 'non_eu');
    $node->set('oe_organisation_non_eu_org_type', 'http://data.europa.eu/uxp/5432');
    $node->save();

    $build = $this->nodeViewBuilder->view($node, 'teaser');
    $html = $this->renderRoot($build);

    $expected_values['meta'] = 'embassy | Acronym';
    $assert->assertPattern($expected_values, $html);
    $assert->assertVariant('thumbnail_secondary', $html);
  }

  /**
   * Creates Contact entity.
   *
   * @param string $name
   *   Entity name. Is used as a parameter for test data.
   * @param string $bundle
   *   Entity bundle.
   * @param int $status
   *   Entity status.
   *
   * @return \Drupal\oe_content_entity_contact\Entity\ContactInterface
   *   Contact entity.
   */
  protected function createContactEntity(string $name, string $bundle, int $status): ContactInterface {
    // Create image for contact.
    $media = $this->createMediaImage($name);

    $contact = Contact::create([
      'bundle' => $bundle,
      'name' => $name,
      'oe_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'address_line1' => "Address $name",
        'postal_code' => '1001',
      ],
      'oe_body' => "Body text $name",
      'oe_email' => "$name@example.com",
      'oe_fax' => "Fax number $name",
      'oe_mobile' => "Mobile number $name",
      'oe_office' => "Office $name",
      'oe_organisation' => "Organisation $name",
      'oe_phone' => "Phone number $name",
      'oe_press_contact_url' => ['uri' => "http://www.example.com/press_contact_$name"],
      'oe_social_media' => [
        [
          'uri' => "http://www.example.com/social_media_$name",
          'title' => "Social media $name",
          'link_type' => 'facebook',
        ],
      ],
      'oe_website' => ['uri' => "http://www.example.com/website_$name"],
      'oe_image' => [
        [
          'target_id' => (int) $media->id(),
          'caption' => "Caption $name",
        ],
      ],
      'status' => $status,
    ]);
    $contact->save();

    return $contact;
  }

  /**
   * Creates media image entity.
   *
   * @param string $name
   *   Name of the image media.
   *
   * @return \Drupal\media\MediaInterface
   *   Media image instance.
   */
  protected function createMediaImage(string $name): MediaInterface {
    // Create file instance.
    $file = file_save_data(file_get_contents(drupal_get_path('theme', 'oe_theme') . '/tests/fixtures/placeholder.png'), "public://placeholder_$name.png");
    $file->setPermanent();
    $file->save();

    $media = Media::create([
      'bundle' => 'image',
      'name' => "Test image $name",
      'oe_media_image' => [
        'target_id' => (int) $file->id(),
        'alt' => "Alternative text $name",
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    return $media;
  }

}
