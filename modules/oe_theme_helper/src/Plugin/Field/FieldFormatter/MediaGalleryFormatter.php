<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a MediaGalleryFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style storage.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   *
   * @SuppressWarnings(PHPMD.ExcessiveParameterList)
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, EntityStorageInterface $image_style_storage, EntityFieldManagerInterface $entityFieldManager, EntityTypeBundleInfoInterface $entityTypeBundleInfo) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $current_user, $image_style_storage);

    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'bundle_settings' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['image_style']['#title'] = $this->t('Thumbnail image style');

    $handler_settings = $this->getFieldSetting('handler_settings') ?? [];
    // If no target bundles or handler settings are present, bail out.
    if (empty($handler_settings['target_bundles'])) {
      return $element;
    }

    $bundle_settings = $this->getSetting('bundle_settings');
    $bundle_info = $this->entityTypeBundleInfo->getBundleInfo('media');
    $element['bundle_settings'] = [
      '#tree' => TRUE,
    ];
    foreach ($handler_settings['target_bundles'] as $bundle_id) {
      $element['bundle_settings'][$bundle_id] = [
        '#type' => 'fieldset',
        '#title' => $bundle_info[$bundle_id]['label'] ?: $bundle_id,
      ];

      // Collect all the fields of type "string" that can be used as source for
      // caption and copyright.
      $candidate_fields = [];
      foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle_id) as $field_name => $definition) {
        if ($definition->getType() === 'string') {
          $candidate_fields[$field_name] = $definition->getLabel() ?: $field_name;
        }
      }

      $element['bundle_settings'][$bundle_id]['caption'] = [
        '#type' => 'select',
        '#title' => $this->t('Caption'),
        '#description' => $this->t('Select the field that will be used as source for the caption text.'),
        '#options' => $candidate_fields,
        '#empty_value' => '',
        '#default_value' => $bundle_settings[$bundle_id]['caption'] ?? '',
      ];

      $element['bundle_settings'][$bundle_id]['copyright'] = [
        '#type' => 'select',
        '#title' => $this->t('Copyright'),
        '#description' => $this->t('Select the field that will be used as source for the copyright text.'),
        '#options' => $candidate_fields,
        '#empty_value' => '',
        '#default_value' => $bundle_settings[$bundle_id]['copyright'] ?? '',
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $bundle_info = $this->entityTypeBundleInfo->getBundleInfo('media');
    foreach ($this->getSetting('bundle_settings') as $bundle_id => $settings) {
      $summary[] = $this->t('@bundle: caption %caption, copyright %copyright', [
        '@bundle' => $bundle_info[$bundle_id]['label'] ?: $bundle_id,
        '%caption' => $settings['caption'] ?: $this->t('not specified'),
        '%copyright' => $settings['copyright'] ?: $this->t('not specified'),
      ]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    return $elements;
  }

}
