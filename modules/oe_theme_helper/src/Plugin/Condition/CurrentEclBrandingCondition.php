<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Condition;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a configurable 'Current ECL branding' condition.
 *
 * This condition checks if the current ECL branding is equal to a given
 * value.
 *
 * @Condition(
 *   id = "oe_theme_helper_current_branding",
 *   label = @Translation("Current ECL branding")
 * )
 */
class CurrentEclBrandingCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

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
   * Constructs a CurrentEclBrandingCondition condition plugin.
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
      'branding' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // We allow for an empty value to be set. By doing that we make sure that
    // no settings about this condition is actually saved in block visibility
    // settings, unless the user explicitly sets one.
    $form['branding'] = [
      '#type' => 'select',
      '#title' => $this->t('ECL branding'),
      '#options' => [
        '' => $this->t('- Any -'),
        'core' => $this->t('Core'),
        'standardised' => $this->t('Standardised'),
      ],
      '#default_value' => $this->configuration['branding'],
      '#description' => t('Choose with which ECL branding this condition should be met.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['branding'] = $form_state->getValue('branding');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['branding'])) {
      return TRUE;
    }
    $theme_name = $this->themeManager->getActiveTheme()->getName();

    $ecl_branding = $this->configFactory->get($theme_name . '.settings')->get('branding');
    return $ecl_branding === $this->configuration['branding'];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (empty($this->configuration['branding'])) {
      return $this->t('The current ECL branding can be set to anything');
    }

    if ($this->isNegated()) {
      return $this->t('The current ECL branding is not @branding', ['@branding' => $this->configuration['branding']]);
    }

    return $this->t('The current ECL branding is @branding', ['@branding' => $this->configuration['branding']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $theme_name = $this->themeManager->getActiveTheme()->getName();

    return Cache::mergeTags(['config:' . $theme_name . '.settings'], parent::getCacheTags());
  }

}
