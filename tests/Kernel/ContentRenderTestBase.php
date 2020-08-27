<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Symfony\Component\DomCrawler\Crawler;

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
    'field',
    'link',
    'file',
    'text',
    'typed_link',
    'maxlength',
    'entity_reference',
    'datetime',
    'node',
    'media',
    'views',
    'entity_browser',
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
    'oe_content_publication',
    'oe_theme_content_news',
    'oe_theme_content_page',
    'oe_theme_content_policy',
    'oe_theme_content_publication',
    'rdf_entity',
    'rdf_skos',
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
      'node',
      'media',
      'filter',
      'rdf_entity',
      'oe_media',
      'media_avportal',
      'oe_media_avportal',
      'typed_link',
    ]);

    // Importing of configs which related to media av_portal output.
    $this->container->get('config.installer')->installDefaultConfig('theme', 'oe_theme');

    $this->installConfig([
      'oe_content',
      'oe_content_timeline_field',
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
