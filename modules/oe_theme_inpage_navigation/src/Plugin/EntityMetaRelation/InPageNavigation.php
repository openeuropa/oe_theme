<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation\Plugin\EntityMetaRelation;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\emr\Entity\EntityMetaInterface;
use Drupal\emr\Plugin\EntityMetaRelationContentFormPluginBase;

/**
 * Provides an entity meta plugin to store if a node with inpage navigation.
 *
 * @EntityMetaRelation(
 *   id = "inpage_navigation",
 *   label = @Translation("Inpage Navigation"),
 *   entity_meta_bundle = "oe_theme_inpage_navigation",
 *   content_form = TRUE,
 *   description = @Translation("Allows to configure a node with inpage navigation."),
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

    $form[$key]['inpage_navigation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Inpage navigation'),
      '#description' => $this->t('Show this content with vertical menu containing (anchored) links to H2-headings on long content pages.'),
      '#default_value' => $entity_meta_wrapper->isInPageNavigation(),
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

    $inpage_navigation = (bool) $form_state->getValue('inpage_navigation');
    // If the entity meta is new, it means we only want to set and save the
    // meta if the form was checked.
    if ($entity_meta->isNew() && $inpage_navigation) {
      $entity_meta_wrapper->setInPageNavigation(TRUE);
      // Attach it to the host entity since the checkbox was checked for the
      // first time.
      $host_entity->get('emr_entity_metas')->attach($entity_meta);
      return;
    }

    // If the entity meta is not new, we set the value regardless of checkbox
    // choice.
    if (!$entity_meta->isNew()) {
      $entity_meta_wrapper->setInPageNavigation($inpage_navigation);
      $host_entity->get('emr_entity_metas')->attach($entity_meta);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fillDefaultEntityMetaValues(EntityMetaInterface $entity_meta): void {
    // We don't want the meta to be created/filled in by default.
  }

}
