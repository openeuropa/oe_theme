<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\CorporateBlocks;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;

/**
 * Base class for corporate block Kernel tests.
 */
abstract class CorporateBlocksTestBase extends AbstractKernelTestBase {

  use RdfDatabaseConnectionTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'oe_content',
    'rdf_entity',
    'rdf_skos',
    'oe_corporate_site_info',
    'oe_corporate_blocks',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpSparql();

    $this->installConfig([
      'rdf_entity',
      'oe_corporate_site_info',
      'oe_corporate_blocks',
    ]);

    $this->installEntitySchema('rdf_entity');
    $this->installEntitySchema('skos_concept');
    $this->installEntitySchema('skos_concept_scheme');

    $graphs = [
      'corporate_body' => 'http://publications.europa.eu/resource/authority/corporate-body',
    ];
    $this->container->get('rdf_skos.skos_graph_configurator')->addGraphs($graphs);

    $this->configFactory = $this->container->get('config.factory');
    $corporate_site_info = $this->configFactory->getEditable('oe_corporate_site_info.settings');
    $corporate_site_info->setData([
      'site_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ACP-EU_JA',
      'content_owners' => ['http://publications.europa.eu/resource/authority/corporate-body/COMMU'],
    ]);
    $corporate_site_info->save();

    $config = $this->configFactory->getEditable('system.site');
    $config->set('name', 'OpenEuropa');
    $config->save();
  }

  /**
   * Render a corporate footer block with test data.
   *
   * @param string $type
   *   The type of block, ec or eu.
   * @param array $test_data
   *   The test data for config and assertion.
   *
   * @return string
   *   The rendered HTML.
   */
  protected function renderCorporateBlocksFooter(string $type, array &$test_data) {
    // Override corporate block footer config with test data.
    $config_name = "oe_corporate_blocks.{$type}_data.footer";
    $fixture_name = "{$type}_footer.yml";
    $block_id = "oe_corporate_blocks_{$type}_footer";

    /* @var $config_obj \Drupal\Core\Config\Config */
    $config_obj = $this->configFactory->getEditable($config_name);
    $test_data = $this->getFixtureContent($fixture_name);
    $config_obj->setData($test_data);
    $config_obj->save();

    // Setup and render footer block.
    $config = [
      'id' => $block_id,
      'label' => 'OpenEuropa footer block',
      'provider' => 'oe_corporate_blocks',
      'label_display' => '0',
    ];
    $build = $this->buildBlock($block_id, $config);

    return $this->renderRoot($build);
  }

}
