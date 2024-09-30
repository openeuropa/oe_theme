<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Block;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\system\Plugin\Block\SystemMenuBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a navigation block to be displayed on ECL site headers.
 *
 * @Block(
 *   id = "oe_theme_helper_site_navigation",
 *   admin_label = @Translation("Site navigation"),
 *   category = @Translation("Site navigation blocks"),
 *   deriver = "Drupal\system\Plugin\Derivative\SystemMenuBlock",
 * )
 */
class SiteNavigationBlock extends SystemMenuBlock {

  /**
   * Configuration factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new SiteNavigationBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, MenuLinkTreeInterface $menu_tree, ?MenuActiveTrailInterface $menu_active_trail = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $menu_tree, $menu_active_trail);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('menu.link_tree'),
      $container->get('menu.active_trail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'level' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->configuration;
    $options = range(0, $this->menuTree->maxDepth());
    unset($options[0]);

    $form['level'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial visibility level'),
      '#default_value' => $config['level'],
      '#options' => $options,
      '#description' => $this->t('The menu is only visible if the menu item for the current page is at this level or below it. Use level 1 to always display this menu.'),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['level'] = $form_state->getValue('level');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $menu_name = $this->getDerivativeId();
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    // Adjust the menu tree parameters based on the block's configuration.
    $level = $this->configuration['level'];
    $parameters->setMinDepth($level);
    $parameters->setMaxDepth(min($level + 1, $this->menuTree->maxDepth()));

    $site_info = $this->configFactory->get('system.site');
    $build = [
      '#theme' => 'oe_theme_helper_site_navigation',
      '#site_name' => $site_info->get('name'),
      '#menu_items' => [],
    ];
    $cacheable_metadata = CacheableMetadata::createFromRenderArray($build);
    $cacheable_metadata->addCacheableDependency($site_info);

    // If menu is empty we only print out the site name.
    $tree = $this->menuTree->load($menu_name, $parameters);
    if (empty($tree)) {
      return $build;
    }

    // Build site tree and process its links.
    $menu_build = $this->buildRenderableMenu($tree);
    $cacheable_metadata->merge(CacheableMetadata::createFromRenderArray($menu_build));
    $build['#menu_items'] = $this->getEclLinks($menu_build['#items'] ?? []);
    $cacheable_metadata->applyTo($build);

    return $build;
  }

  /**
   * Build menu renderable array.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The menu tree, as returned from MenuLinkTreeInterface::load().
   *
   * @return array
   *   A renderable array.
   */
  protected function buildRenderableMenu(array $tree): array {
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);

    return $this->menuTree->build($tree);
  }

  /**
   * Massage data to be compliant with ECL navigation menu data structure.
   *
   * For each menu tree item we rename the following properties:
   *
   * - "title" into "label"
   * - "url" into "href"
   * - "in_active_trail" into "is_current"
   *
   * @param array $items
   *   Menu tree renderable array.
   *
   * @return array
   *   Array of ECL-compatible links.
   */
  protected function getEclLinks(array $items): array {
    $links = array_map(function ($item) {
      return [
        'label' => $item['title'],
        'href' => $item['url'],
        'is_current' => $item['in_active_trail'],
      ];
    }, $items);

    foreach ($items as $name => $link) {
      $links[$name]['children'] = array_map(function ($item) {
        return [
          'label' => $item['title'],
          'href' => $item['url'],
          'is_current' => $item['in_active_trail'],
        ];
      }, $items[$name]['below']);
    }

    return $links;
  }

}
