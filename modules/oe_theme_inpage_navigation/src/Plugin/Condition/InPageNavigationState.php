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
 * Provides a configurable 'In-page Navigation State' condition.
 *
 * This condition checks if the in-page navigation is enabled for the node.
 *
 * @Condition(
 *   id = "oe_theme_inpage_navigation_state",
 *   label = @Translation("In-page navigation"),
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
      'inpage_navigation_state' => FALSE,
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
      '#options' => [
        $this->t('Disabled'),
        $this->t('Enabled'),
      ],
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

    // If we don't have a node, we return FALSE, unless the plugin is configured
    // to be negated, in which case, it's the opposite.
    if (!$node instanceof NodeInterface) {
      return $this->isNegated();
    }

    return InPageNavigationHelper::isInPageNavigationEnabled($node) === (bool) $this->configuration['inpage_navigation_state'];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $state = $this->configuration['inpage_navigation_state'] ? $this->t('enabled') : $this->t('disabled');
    if ($this->isNegated()) {
      return $this->t('The in-page navigation should not be @state', ['@state' => $state]);
    }

    return $this->t('The in-page navigation should be @state', ['@state' => $state]);
  }

}
