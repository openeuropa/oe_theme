<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\media\MediaInterface;
use Drupal\oe_theme\ValueObject\GalleryItemValueObject;
use Drupal\oe_theme_helper\MediaDataExtractorPluginManagerInterface;
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
   * The media data extractor plugin manager.
   *
   * @var \Drupal\oe_theme_helper\MediaDataExtractorPluginManagerInterface
   */
  protected $mediaDataExtractorManager;

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
   * @param \Drupal\oe_theme_helper\MediaDataExtractorPluginManagerInterface $mediaDataExtractorManager
   *   The media data extractor plugin manager.
   *
   * @SuppressWarnings(PHPMD.ExcessiveParameterList)
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, EntityStorageInterface $image_style_storage, EntityFieldManagerInterface $entityFieldManager, EntityTypeBundleInfoInterface $entityTypeBundleInfo, MediaDataExtractorPluginManagerInterface $mediaDataExtractorManager) {
    // @deprecated File url generator should be added to the signature and properly injected, as per \Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter.
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $current_user, $image_style_storage, \Drupal::service('file_url_generator'));

    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->mediaDataExtractorManager = $mediaDataExtractorManager;
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
      $container->get('entity_type.bundle.info'),
      $container->get('plugin.manager.oe_theme.media_data_extractor')
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
        '#required' => TRUE,
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
    // @todo refactor to not use the parent method.
    $elements = parent::viewElements($items, $langcode);

    // Recollect the entities to view. If none are found, the elements array
    // already contains all the cache information and we can safely return it.
    $entities = $this->getEntitiesToView($items, $langcode);
    if (empty($entities)) {
      return $elements;
    }

    $cacheable_metadata = CacheableMetadata::createFromRenderArray($elements);
    // Loop through the generated thumbnail URLs by the parent formatter.
    // Only the available thumbnails are present, and they are keyed by delta
    // so we can retrieve the originating media.
    $items = [];
    foreach (array_keys($elements) as $delta) {
      /** @var \Drupal\media\MediaInterface $media */
      $media = $entities[$delta];

      $extractor = $this->mediaDataExtractorManager->createInstanceByMediaBundle($media->bundle(), [
        'thumbnail_image_style' => $this->getSetting('image_style'),
      ]);
      $thumbnail = $extractor->getThumbnail($media);

      $values = [
        'thumbnail' => $thumbnail,
        'source' => $extractor->getSource($media) ?? '',
        'type' => $extractor->getGalleryMediaType(),
      ];

      // Collect the attributes from the fields specified in the configuration.
      $values += $this->extractAttributes($media);

      // Provide a default caption value, if none is present.
      if ($values['caption'] === ' ') {
        $values['caption'] = $media->label();
      }

      $items[$delta] = GalleryItemValueObject::fromArray($values);
      // Add the cacheability information of the gallery item itself.
      $cacheable_metadata->addCacheableDependency($items[$delta]);
      // Collect the cache information from the render array generated by the
      // parent formatter.
      $cacheable_metadata->addCacheableDependency(CacheableMetadata::createFromRenderArray($elements[$delta]));
    }

    $elements = [
      '#type' => 'pattern',
      '#id' => 'gallery',
      '#items' => $items,
    ];
    $cacheable_metadata->applyTo($elements);

    return $elements;
  }

  /**
   * Extract gallery item attributes based on the formatter configuration.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media where to extract the data from.
   *
   * @return array
   *   An array of attributes to be used in gallery items.
   */
  protected function extractAttributes(MediaInterface $media): array {
    // Map configuration strings to pattern property names.
    $attribute_mapping = [
      'caption' => 'caption',
      'copyright' => 'meta',
    ];

    $bundle_settings = $this->getSetting('bundle_settings');
    $values = [];
    foreach ($attribute_mapping as $attribute => $key) {
      $field = $bundle_settings[$media->bundle()][$attribute] ?? '';
      $values[$key] = $field !== '' && $media->hasField($field) && !$media->get($field)->isEmpty()
        ? $media->get($field)->first()->getString()
        // Due to limitations in the current ECL gallery implementation, all
        // the attributes need to be specified, so we pass an empty string.
        // If attributes are missing, the javascript modal won't show the
        // correct caption and copyrights when looping through medias.
        : ' ';
    }

    return $values;
  }

}
