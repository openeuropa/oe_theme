<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\field_group\FieldGroupFormatter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Theme\ComponentPluginManager;
use Drupal\field_group\FieldGroupFormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for field group formatters that use SDC components for rendering.
 */
abstract class PatternFormatterBase extends FieldGroupFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The SDC component plugin manager.
   *
   * @var \Drupal\Core\Theme\ComponentPluginManager
   */
  protected ComponentPluginManager $componentManager;

  /**
   * PatternFormatterBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Theme\ComponentPluginManager $component_manager
   *   The SDC component plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ComponentPluginManager $component_manager) {
    parent::__construct($plugin_id, $plugin_definition, $configuration['group'], $configuration['settings'], $configuration['label']);
    $this->configuration = $configuration;
    $this->componentManager = $component_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.sdc')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'label' => '',
      'variant' => '',
      'display_label' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $component_id = 'oe_theme:' . $this->getPatternId();
    $definition = $this->componentManager->getDefinition($component_id);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Field group label'),
      '#default_value' => $this->label,
    ];

    $variants = $definition['variants'] ?? [];
    if (!empty($variants)) {
      $options = [];
      foreach ($variants as $variant_id => $variant) {
        $options[$variant_id] = $variant['title'] ?? $variant_id;
      }
      $form['variant'] = [
        '#title' => $this->t('Variant'),
        '#type' => 'select',
        '#options' => $options,
        '#default_value' => $this->getSetting('variant'),
      ];
    }

    $form['display_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display field group label'),
      '#default_value' => (bool) $this->getSetting('display_label'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    if ($this->label) {
      $summary[] = $this->t('Label: @label', ['@label' => $this->label]);
    }

    if ($this->getSetting('variant')) {
      $summary[] = $this->t('Variant: @variant', ['@variant' => $this->getSetting('variant')]);
    }

    if ($this->getSetting('display_label')) {
      $summary[] = $this->t('Display field group label');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);

    $props = $this->getFields($element, $rendering_object);
    $pattern = [
      '#type' => 'component',
      '#component' => 'oe_theme:' . $this->getPatternId(),
      '#props' => $props,
      '#variant' => $this->getSetting('variant'),
      '#context' => [
        'type' => 'field_group',
        'group_name' => $element['#group_name'],
        'entity_type' => $element['#entity_type'],
        'bundle' => $element['#bundle'],
        'view_mode' => $this->group->mode,
      ],
    ];

    // Remove all renderable elements, while keeping render metadata as that can
    // be used to further manipulate the render array.
    foreach (Element::children($element) as $key) {
      unset($element[$key]);
    }
    $element += [
      'pattern' => $pattern,
    ];

    if (!$this->getSetting('display_label') || !$this->label) {
      return;
    }

    $element['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#value' => $this->label,
      '#attributes' => ['class' => ['ecl-u-type-heading-2']],
      '#weight' => -1,
    ];
  }

  /**
   * Return pattern ID for the current formatter plugin.
   *
   * @return string
   *   Pattern ID.
   */
  abstract protected function getPatternId(): string;

  /**
   * Return list of fields for the current pattern.
   *
   * @param array $element
   *   Field group render element.
   * @param object $rendering_object
   *   Field group rendering object.
   *
   * @return array
   *   Pattern fields to be rendered, or an empty array if none.
   */
  abstract protected function getFields(array &$element, $rendering_object): array;

}
