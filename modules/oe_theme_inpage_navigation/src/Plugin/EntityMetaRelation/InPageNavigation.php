<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation\Plugin\EntityMetaRelation;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\emr\Entity\EntityMetaInterface;
use Drupal\emr\Plugin\EntityMetaRelationContentFormPluginBase;
use Drupal\oe_theme_inpage_navigation\InPageNavigationHelper;

/**
 * Provides an entity meta plugin to store if a node with inpage navigation.
 *
 * @EntityMetaRelation(
 *   id = "inpage_navigation",
 *   label = @Translation("In-page Navigation"),
 *   entity_meta_bundle = "oe_theme_inpage_navigation",
 *   content_form = TRUE,
 *   description = @Translation("Allows to configure a node with in-page navigation."),
 *   entity_meta_wrapper_class = "\Drupal\oe_theme_inpage_navigation\InPageNavigationWrapper",
 * )
 */
class InPageNavigation extends EntityMetaRelationContentFormPluginBase {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * {@inheritdoc}
   */
  public function build(array $form, FormStateInterface $form_state, ContentEntityInterface $entity): array {
    $key = $this->getFormKey();
    $this->buildFormContainer($form, $form_state, $key);

    $entity_meta_bundle = $this->getPluginDefinition()['entity_meta_bundle'];

    /** @var \Drupal\emr\Field\EntityMetaItemListInterface $entity_meta_list */
    $entity_meta_list = $entity->get('emr_entity_metas');
    /** @var \Drupal\emr\Entity\EntityMetaInterface $entity_meta */
    $entity_meta = $entity_meta_list->getEntityMeta($entity_meta_bundle);
    /** @var \Drupal\oe_theme_inpage_navigation\InPageNavigationWrapper $entity_meta_wrapper */
    $entity_meta_wrapper = $entity_meta->getWrapper();

    $form[$key]['override_inpage_navigation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override default settings'),
      '#description' => InPageNavigationHelper::getDefaultInPageNavigationSettings($entity) ? $this->t('Enabled by default.') : $this->t('Disabled by default.'),
      '#default_value' => !$entity_meta->isNew(),
    ];

    $form[$key]['inpage_navigation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable in-page navigation on this page'),
      '#default_value' => $entity_meta_wrapper->isInPageNavigationEnabled(),
      '#states' => [
        'visible' => [
          ':input[name="override_inpage_navigation"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Set the entity meta so we use it in the submit handler.
    $form_state->set($entity_meta_bundle . '_entity_meta', $entity_meta);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state): void {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $host_entity */
    $host_entity = $form_state->getFormObject()->getEntity();
    $entity_meta_bundle = $this->getPluginDefinition()['entity_meta_bundle'];

    /** @var \Drupal\emr\Entity\EntityMetaInterface $entity_meta */
    $entity_meta = $form_state->get($entity_meta_bundle . '_entity_meta');
    /** @var \Drupal\oe_theme_inpage_navigation\InPageNavigationWrapper $entity_meta_wrapper */
    $entity_meta_wrapper = $entity_meta->getWrapper();

    $override_inpage_navigation = (bool) $form_state->getValue('override_inpage_navigation');
    $inpage_navigation = (bool) $form_state->getValue('inpage_navigation');
    // Set the value in case the override checkbox has been checked.
    if ($override_inpage_navigation) {
      $entity_meta_wrapper->setInPageNavigation($inpage_navigation);
      // Attach it to the host entity since the checkbox was checked for the
      // first time.
      $host_entity->get('emr_entity_metas')->attach($entity_meta);
      return;
    }
    elseif (!$entity_meta->isNew() && !$override_inpage_navigation) {
      $host_entity->get('emr_entity_metas')->detach($entity_meta);
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fillDefaultEntityMetaValues(EntityMetaInterface $entity_meta): void {
    // We don't want the meta to be created/filled in by default.
  }

}
