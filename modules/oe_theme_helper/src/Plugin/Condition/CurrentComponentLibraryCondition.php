<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Condition;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a configurable 'Current component library' condition.
 *
 * This condition checks if the current component library is equal to a given
 * value.
 *
 * @Condition(
 *   id = "oe_theme_helper_current_component_library",
 *   label = @Translation("Current component library")
 * )
 */
class CurrentComponentLibraryCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Constructs a CurrentComponentLibraryCondition condition plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, ThemeManagerInterface $theme_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->themeManager = $theme_manager;
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
      $container->get('theme.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'component_library' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // We allow for an empty value to be set. By doing that we make sure that
    // no settings about this condition is actually saved in block visibility
    // settings, unless the user explicitly sets one.
    $form['component_library'] = [
      '#type' => 'select',
      '#title' => $this->t('Component library'),
      '#options' => [
        '' => $this->t('- Any -'),
        'ec' => $this->t('European Commission'),
        'eu' => $this->t('European Union'),
      ],
      '#default_value' => $this->configuration['component_library'],
      '#description' => t('Choose with which component library this condition should be met.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['component_library'] = $form_state->getValue('component_library');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['component_library'])) {
      return TRUE;
    }
    $theme = $this->themeManager->getActiveTheme()->getName();

    if (empty($theme)) {
      return;
    }

    $component_library = $this->configFactory->get($theme . '.settings')->get('component_library');
    return $component_library === $this->configuration['component_library'];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (empty($this->configuration['component_library'])) {
      return $this->t('The current component library can be set to anything');
    }

    if ($this->isNegated()) {
      return $this->t('The current component library is not @component_library', ['@component_library' => $this->configuration['component_library']]);
    }

    return $this->t('The current component library is @component_library', ['@component_library' => $this->configuration['component_library']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $theme = $this->themeManager->getActiveTheme()->getName();

    if (empty($theme)) {
      return parent::getCacheTags();
    }

    return Cache::mergeTags(['config:' . $theme . '.settings'], parent::getCacheTags());
  }

}
