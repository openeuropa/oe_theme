<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Base class for testing the content being rendered.
 */
abstract class ContentRenderTestBase extends AbstractKernelTestBase {

  use RdfDatabaseConnectionTrait;

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
    'entity_reference',
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
    'rdf_entity',
    'rdf_skos',
    'file_link',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpSparql();

    $this->installEntitySchema('node');
    $this->installSchema('file', 'file_usage');
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('media');
    $this->installEntitySchema('file');

    $this->installConfig([
      'file',
      'field',
      'entity_reference',
      'entity_reference_revisions',
      'composite_reference',
      'node',
      'media',
      'filter',
      'rdf_entity',
      'oe_media',
      'media_avportal',
      'oe_media_avportal',
      'typed_link',
      'address',
    ]);

    // Importing of configs which related to media av_portal output.
    $this->container->get('config.installer')->installDefaultConfig('theme', 'oe_theme');

    $this->container->get('module_handler')->loadInclude('oe_content_documents_field', 'install');
    oe_content_documents_field_install();

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
      ->grantPermission('view media')
      ->save();

    $this->installEntitySchema('rdf_entity');
    $this->installEntitySchema('skos_concept');
    $this->installEntitySchema('skos_concept_scheme');

    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
    $this->nodeViewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('node');
  }

}
