<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\file\Entity\File;
use Drupal\media\MediaInterface;

/**
 * Plugin implementation of the 'Featured media as entity' formatter.
 *
 * @FieldFormatter(
 *   id = "oe_featured_media_entity_view",
 *   label = @Translation("Rendered entity"),
 *   description = @Translation("Display the entity of the referenced media and the caption."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaRenderedEntityFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewElement($item);
    }

    return $elements;
  }

  /**
   * Renders a single field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The individual field item.
   *
   * @return array
   *   The ECL media-container parameters.
   */
  protected function viewElement(FieldItemInterface $item): array {
    $params = [];
    $params['description'] = $item->caption;

    if (!$item->entity instanceof MediaInterface) {
      return [];
    }

    switch ($item->entity->bundle()) {
      case 'remote_video':
        $params['embedded_media'] = $item->entity->get('oe_media_oembed_video')->view([
          'type' => 'oembed',
          'label' => 'hidden',
        ]);
        break;

      case 'image':
        $file = File::load($item->target_id);
        $params['alt'] = $item->entity->get('oe_media_image')->getValue()[0]['alt'];
        $params['image'] = $file->url();
        break;
    }

    return [
      '#theme' => 'oe_theme_helper_featured_media',
      '#params' => $params,
    ];
  }

}
