<?php

namespace Drupal\oe_theme_demo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides ECL demo block.
 *
 * @Block(
 *   id = "oe_theme_demo_block",
 *   admin_label = @Translation("OpenEuropa ECL Demo Block"),
 *   category = @Translation("OpenEuropa"),
 *   deriver = "Drupal\oe_theme_demo\Plugin\Derivative\DemoBlock"
 * )
 */
class DemoBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return ['#theme' => $this->getThemeHook()];
  }

  /**
   * Get theme hook of current demo block plugin.
   *
   * @return string
   *   Theme hook.
   */
  protected function getThemeHook() {
    return "demo_block_" . $this->getPluginDefinition()['id'];
  }

}
