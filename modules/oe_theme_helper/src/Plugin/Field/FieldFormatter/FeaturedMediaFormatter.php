<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media\MediaInterface;
use Drupal\media\Plugin\media\Source\Image;
use Drupal\media\Plugin\media\Source\OEmbed;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;

/**
 * Display a featured media field using the ECL media container.
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
   * Constructs a FormatterBase object.
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
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['label'], $configuration['view_mode'], $configuration['third_party_settings'], $container->get('entity_type.manager'), $container->get('entity.repository'));
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['image_style'] = [
      '#title' => t('Image style in case Media is image.'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => t('None (original image)'),
      '#options' => image_style_options(FALSE),
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
      $summary[] = t('Style: @style for image values', ['@style' => $image_styles[$image_style_setting]]);
    }
    else {
      $summary[] = t('Original for image values');
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
    $build = ['#theme' => 'oe_theme_helper_featured_media'];
    $params = ['description' => $item->caption];
    $media = $item->entity;
    $cacheability = CacheableMetadata::createFromRenderArray($build);

    if (!$media instanceof MediaInterface) {
      return [];
    }

    // Retrieve the correct media translation.
    $media = $this->entityRepository->getTranslationFromContext($media, $langcode);

    // Caches are handled by the formatter usually. Since we are not rendering
    // the original render arrays, we need to propagate our caches to the
    // oe_theme_helper_featured_media template.
    $cacheability->addCacheableDependency($media);

    // Get the media source.
    $source = $media->getSource();

    if ($source instanceof OEmbed) {
      // Pass it as embeddable data only if it is of type video or html.
      $oembed_type = $source->getMetadata($media, 'type');

      if (!in_array($oembed_type, ['video', 'html'])) {
        return [];
      }

      // Default video aspect ratio is set to 16:9.
      $params['ratio'] = '16:9';
      // Load information about the media and the display.
      $media_type = $this->entityTypeManager->getStorage('media_type')->load($media->bundle());
      $cacheability->addCacheableDependency($media_type);
      $source_field = $source->getSourceFieldDefinition($media_type);
      $display = EntityViewDisplay::collectRenderDisplay($media, 'oe_theme_main_content');
      $cacheability->addCacheableDependency($display);
      $display_options = $display->getComponent($source_field->getName());
      $params['embedded_media'] = $media->{$source_field->getName()}->view($display_options);
      $build['#params'] = $params;
      $cacheability->applyTo($build);

      return $build;
    }

    if ($source instanceof Image) {
      /** @var \Drupal\image\Plugin\Field\FieldType\ImageItem $thumbnail */
      $thumbnail = $media->get('thumbnail')->first();
      /** @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $file */
      $file = $thumbnail->get('entity')->getTarget();
      $image_style = $this->getSetting('image_style');
      $style = $this->entityTypeManager->getStorage('image_style')->load($image_style);

      if ($style) {
        // Use image style url if set.
        $image_url = $style->buildUrl($file->get('uri')->getString());
        $cacheability->addCacheableDependency($image_style);
      }
      else {
        // Use original file url.
        $image_url = file_create_url($file->get('uri')->getString());
      }

      $params['alt'] = $media->get('oe_media_image')->getValue()[0]['alt'];
      $params['image'] = $image_url;
      $build['#params'] = $params;
      $cacheability->applyTo($build);

      return $build;
    }
  }

}
