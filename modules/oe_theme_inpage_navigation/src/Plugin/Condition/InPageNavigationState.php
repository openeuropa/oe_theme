<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\oe_theme_inpage_navigation\InPageNavigationHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a configurable 'Inpage Navigation State' condition.
 *
 * This condition checks if the inpage navigation for node is enabled.
 *
 * @Condition(
 *   id = "oe_theme_inpage_navigation_state",
 *   label = @Translation("Inpage navigation"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", required = FALSE)
 *   }
 * )
 */
class InPageNavigationState extends ConditionPluginBase implements ContainerFactoryPluginInterface {

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
  public function defaultConfiguration() {
    return [
      'inpage_navigation_state' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['inpage_navigation_state'] = [
      '#type' => 'radios',
      '#title' => $this->t('Inpage navigation'),
      '#default_value' => $this->configuration['inpage_navigation_state'],
      '#options' => [$this->t('Disabled'), $this->t('Enabled')],
      '#description' => $this->t('Choose with which inpage navigation state this condition should be met.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->hasValue('inpage_navigation_state')) {
      $this->configuration['inpage_navigation_state'] = $form_state->getValue('inpage_navigation_state');
    }
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $node = $this->getContextValue('node');
    if ($this->configuration['inpage_navigation_state'] === NULL || !$node instanceof NodeInterface) {
      return TRUE;
    }

    return InPageNavigationHelper::isInPageNavigation($node) === $this->configuration['inpage_navigation_state'];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (is_null($this->configuration['inpage_navigation_state'])) {
      return $this->t('Any inpage navigation state');
    }

    $state = $this->configuration['inpage_navigation_state'] ? $this->t('enabled') : $this->t('disabled');
    if ($this->isNegated()) {
      return $this->t('The inpage navigation should not be @state', ['@state' => $state]);
    }

    return $this->t('The inpage navigation should be @state', ['@state' => $state]);
  }

}
