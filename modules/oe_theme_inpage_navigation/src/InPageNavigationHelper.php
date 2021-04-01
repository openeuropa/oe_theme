<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_inpage_navigation;

use Drupal\node\NodeInterface;

/**
 * Helper class for the in-page navigation functionality.
 */
class InPageNavigationHelper {

  /**
   * Returns default setting for in-page nav for the node bundle.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return bool
   *   Whether the in-page nav is enabled in a node by default.
   */
  public static function isInPageNavigationEnabledByDefault(NodeInterface $node): bool {
    return $node->get('type')->entity->getThirdPartySetting('oe_theme_inpage_navigation', 'enabled', FALSE);
  }

  /**
   * Returns whether a given node has in-page navigation enabled.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return bool
   *   Whether in-page navigation is enabled or not.
   */
  public static function isInPageNavigationEnabled(NodeInterface $node): bool {
    /** @var \Drupal\emr\Field\EntityMetaItemListInterface $entity_meta_list */
    $entity_meta_list = $node->get('emr_entity_metas');

    /** @var \Drupal\emr\Entity\EntityMetaInterface $entity_meta */
    $entity_meta = $entity_meta_list->getEntityMeta('oe_theme_inpage_navigation');

    /** @var \Drupal\oe_theme_inpage_navigation\InPageNavigationWrapper $entity_meta_wrapper */
    $entity_meta_wrapper = $entity_meta->getWrapper();

    if ($entity_meta->isNew()) {
      return self::isInPageNavigationEnabledByDefault($entity_meta->getHostEntity());
    }

    return $entity_meta_wrapper->isInPageNavigationEnabled();
  }

  /**
   * Enables in-page navigation on a given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   */
  public static function enableInPageNavigation(NodeInterface $node): void {
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
