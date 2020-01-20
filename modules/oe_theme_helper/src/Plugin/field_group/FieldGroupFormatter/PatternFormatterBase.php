<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field_group\FieldGroupFormatterBase;
use Drupal\ui_patterns\UiPatternsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for field group formatters that use a pattern for rendering.
 */
abstract class PatternFormatterBase extends FieldGroupFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * UI Patterns manager.
   *
   * @var \Drupal\ui_patterns\UiPatternsManager
   */
  protected $patternsManager;

  /**
   * PatternFormatterBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\ui_patterns\UiPatternsManager $patterns_manager
   *   UI Patterns manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UiPatternsManager $patterns_manager) {
    parent::__construct($plugin_id, $plugin_definition, $configuration['group'], $configuration['settings'], $configuration['label']);
    $this->configuration = $configuration;
    $this->patternsManager = $patterns_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.ui_patterns')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'label' => '',
      'variant' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $pattern = $this->patternsManager->getDefinition($this->getPatternId());

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => t('Field group label'),
      '#default_value' => $this->label,
    ];

    if ($pattern->hasVariants()) {
      $form['variant'] = [
        '#title' => $this->t('Variant'),
        '#type' => 'select',
        '#options' => $pattern->getVariantsAsOptions(),
        '#default_value' => $this->getSetting('variant'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    if ($this->getSetting('label')) {
      $summary[] = $this->t('Label: @label', ['@label' => $this->getSetting('label')]);
    }

    if ($this->getSetting('variant')) {
      $summary[] = $this->t('Variant: @variant', ['@variant' => $this->getSetting('variant')]);
    }

    return $summary;
  }

  /**
   * Return pattern ID for the current formatter plugin.
   *
   * @return string
   *   Pattern ID.
   */
  abstract protected function getPatternId(): string;

}
