<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Media gallery field formatter.
 *
 * The formatter renders a media field using the ECL media gallery component.
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_media_gallery",
 *   label = @Translation("Gallery"),
 *   description = @Translation("Display media entities using the ECL media gallery component."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MediaGalleryFormatter extends MediaThumbnailUrlFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['image_style']['#title'] = $this->t('Thumbnail image style');

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    return $elements;
  }

}
