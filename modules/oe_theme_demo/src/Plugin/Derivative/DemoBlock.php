<?php

namespace Drupal\oe_theme_demo\Plugin\Derivative;

use Symfony\Component\Yaml\Yaml;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DemoBlock.
 */
class DemoBlock extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * OEDemoBlock constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    $base_plugin_id
  ) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_def) {
    $config = Yaml::parse(
      file_get_contents(drupal_get_path('module', 'oe_theme_demo') . '/ecl.yml')
    );

    $this->derivatives = [];
    foreach ($config['blocks'] as $id => $block) {
      $this->derivatives[$id] = $base_plugin_def;
      $this->derivatives[$id]['admin_label'] = $block['label'];

      $content = $block['content'];
      if (is_array($content)) {
        $content = $this->renderer->renderRoot($content);
      }

      $this->derivatives[$id]['content'] = $content;
    }

    return parent::getDerivativeDefinitions($base_plugin_def);
  }

}
