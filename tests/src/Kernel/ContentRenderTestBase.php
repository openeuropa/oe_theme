<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;
use Drupal\media\MediaInterface;
use Drupal\oe_content_entity\Entity\CorporateEntityInterface;
use Drupal\oe_content_entity_contact\Entity\Contact;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Base class for testing the content being rendered.
 */
abstract class ContentRenderTestBase extends MultilingualAbstractKernelTestBase {

  use SparqlConnectionTrait;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The node view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewBuilder;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'address',
    'field',
    'field_group',
    'link',
    'file',
    'text',
    'typed_link',
    'maxlength',
    'entity_reference_revisions',
    'composite_reference',
    'inline_entity_form',
    'datetime',
    'node',
    'media',
    'views',
    'entity_browser',
    'extra_field',
    'media_avportal',
    'media_avportal_mock',
    'filter',
    'oe_media',
    'oe_media_avportal',
    'oe_content',
    'oe_content_entity',
    'oe_content_timeline_field',
    'oe_content_news',
    'oe_content_page',
    'oe_content_policy',
    'oe_content_departments_field',
    'oe_content_documents_field',
    'oe_content_publication',
    'oe_content_reference_code_field',
    'oe_content_entity_contact',
    'oe_content_featured_media_field',
    'oe_theme_content_news',
    'oe_theme_content_page',
    'oe_theme_content_policy',
    'oe_theme_content_publication',
    'sparql_entity_storage',
    'rdf_skos',
    'file_link',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected function bootEnvironment(): void {
    parent::bootEnvironment();
    $this->setUpSparql();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installSchema('file', 'file_usage');
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');

    $this->installConfig([
      'file',
      'field',
      'entity_reference_revisions',
      'composite_reference',
      'node',
      'media',
      'filter',
      'oe_media',
      'media_avportal',
      'oe_media_avportal',
      'typed_link',
      'address',
    ]);

    // Importing of configs which related to media av_portal output.
    $this->container->get('config.installer')->installDefaultConfig('theme', 'oe_theme');

    $this->container->get('module_handler')->loadInclude('oe_content_documents_field', 'install');
    oe_content_documents_field_install(FALSE);

    $this->installConfig([
      'oe_content',
      'oe_content_entity_contact',
      'oe_content_timeline_field',
      'oe_content_departments_field',
      'oe_content_reference_code_field',
      'oe_content_featured_media_field',
      'oe_content_news',
      'oe_content_page',
      'oe_content_policy',
      'oe_content_publication',
      'oe_theme_content_news',
      'oe_theme_content_page',
      'oe_theme_content_policy',
      'oe_theme_content_publication',
    ]);

    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('bypass node access')
      ->grantPermission('view published skos concept entities')
      ->grantPermission('view media')
      ->save();

    \Drupal::moduleHandler()->loadInclude('oe_content', 'install');
    oe_content_install(FALSE);

    $this->installEntitySchema('skos_concept');
    $this->installEntitySchema('skos_concept_scheme');

    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
    $this->nodeViewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('node');
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
    $file = \Drupal::service('file.repository')->writeData(file_get_contents(\Drupal::service('extension.list.theme')->getPath('oe_theme') . '/tests/fixtures/placeholder.png'), "public://placeholder_$name.png");
    $file->setPermanent();
    $file->save();

    $media = $this->container->get('entity_type.manager')->getStorage('media')->create([
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
  protected function createContactEntity(string $name, string $bundle, int $status = CorporateEntityInterface::PUBLISHED): ContactInterface {
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

}
