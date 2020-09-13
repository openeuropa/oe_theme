<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\oe_theme\ValueObject\MediaValueObject;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Display a featured media field using the ECL media container.
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 *
 * @FieldFormatter(
 *   id = "oe_theme_helper_featured_media_formatter",
 *   label = @Translation("Media container"),
 *   description = @Translation("Display a featured media field using the ECL media container."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaFormatter extends EntityReferenceFormatterBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a FeaturedMediaFormatter object.
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
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['label'], $configuration['view_mode'], $configuration['third_party_settings'], $container->get('entity_type.manager'), $container->get('entity.repository'), $container->get('entity_display.repository'));
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'view_mode' => 'oe_theme_main_content',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    foreach ($this->entityDisplayRepository->getViewModes('media') as $key => $value) {
      if ($value['status']) {
        $view_mode[$key] = $value['label'];
      }
    }

    $element['image_style'] = [
      '#title' => t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => t('None (original image)'),
      '#options' => image_style_options(FALSE),
      '#description' => t('Image style to be used if the Media is an image.'),
    ];
    $element['view_mode'] = [
      '#title' => $this->t('View mode'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
      '#options' => $view_mode,
      '#description' => $this->t('View mode of the media element.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    // Styles could be lost because of enabled/disabled modules that defines
    // their styles in code.
    $image_style_setting = $this->getSetting('image_style');
    if (isset($image_styles[$image_style_setting])) {
      $summary[] = t('@image_style image style', ['@image_style' => $image_styles[$image_style_setting]]);
    }
    else {
      $summary[] = t('Original image style.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewElement($item, $langcode);
    }

    return $elements;
  }

  /**
   * Renders a single field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The individual field item.
   * @param string $langcode
   *   The language code.
   *
   * @return array
   *   The ECL media-container parameters.
   */
  protected function viewElement(FieldItemInterface $item, string $langcode): array {
    $pattern = [];
    $media = $item->entity;

    if (!$media instanceof MediaInterface) {
      return $pattern;
    }

    $image_style = $this->getSetting('image_style');
    $view_mode = $this->getSetting('view_mode');
    $cacheability = CacheableMetadata::createFromRenderArray($pattern);
    // Retrieve the correct media translation.
    $media = $this->entityRepository->getTranslationFromContext($media, $langcode);
    // Caches are handled by the formatter usually. Since we are not rendering
    // the original render arrays, we need to propagate our caches.
    $cacheability->addCacheableDependency($media);
    $cacheability->addCacheableDependency($this->entityTypeManager->getStorage('media_type')->load($media->bundle()));
    $cacheability->addCacheableDependency(EntityViewDisplay::collectRenderDisplay($media, $view_mode));

    $pattern = [
      '#type' => 'pattern',
      '#id' => 'media_container',
      '#fields' => [
        'media' => MediaValueObject::fromMediaObject($media, $image_style, $view_mode),
        'description' => $item->caption,
      ],
    ];

    $cacheability->applyTo($pattern);

    return $pattern;
  }

}
