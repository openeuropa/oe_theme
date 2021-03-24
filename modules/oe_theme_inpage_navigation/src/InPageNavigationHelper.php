<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation;

use Drupal\node\NodeInterface;

/**
 * Helper class for the inpage navigation functionality.
 */
class InPageNavigationHelper {

  /**
   * Returns if a given node with inpage navigation.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return bool
   *   Whether it's content with inpage navigation.
   */
  public static function isInPageNavigation(NodeInterface $node): bool {
    /** @var \Drupal\emr\Field\EntityMetaItemListInterface $entity_meta_list */
    $entity_meta_list = $node->get('emr_entity_metas');

    /** @var \Drupal\emr\Entity\EntityMetaInterface $entity_meta */
    $entity_meta = $entity_meta_list->getEntityMeta('oe_theme_inpage_navigation');

    /** @var \Drupal\oe_theme_inpage_navigation\InPageNavigationWrapper $entity_meta_wrapper */
    $entity_meta_wrapper = $entity_meta->getWrapper();
    return $entity_meta_wrapper->isInPageNavigation();
  }

  /**
   * Sets a node with inpage navigation.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   */
  public static function setInPageNavigation(NodeInterface $node): void {
    /** @var \Drupal\emr\Field\EntityMetaItemListInterface $entity_meta_list */
    $entity_meta_list = $node->get('emr_entity_metas');

    /** @var \Drupal\emr\Entity\EntityMetaInterface $entity_meta */
    $entity_meta = $entity_meta_list->getEntityMeta('oe_theme_inpage_navigation');

    /** @var \Drupal\oe_theme_inpage_navigation\InPageNavigationWrapper $entity_meta_wrapper */
    $entity_meta_wrapper = $entity_meta->getWrapper();
    $entity_meta_wrapper->setInPageNavigation(TRUE);
    $entity_meta_list->attach($entity_meta);
  }

}
