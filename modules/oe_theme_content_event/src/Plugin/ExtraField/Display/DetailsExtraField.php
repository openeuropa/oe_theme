<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying event details as a list of icons and text.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_details",
 *   label = @Translation("Details"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class DetailsExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Entity view builder object for "oe_contact" entity type.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $contactViewBuilder;

  /**
   * Entity view builder object for "oe_venue" entity type.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $venueViewBuilder;

  /**
   * DetailsExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity view builder object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->contactViewBuilder = $entity_type_manager->getViewBuilder('oe_contact');
    $this->venueViewBuilder = $entity_type_manager->getViewBuilder('oe_venue');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Details');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    // The pattern will take care of not displaying empty items.
    return [
      '#type' => 'pattern',
      '#id' => 'icons_with_text',
      '#fields' => [
        'items' => [
          [
            'icon' => 'file',
            'text' => $this->getRenderableSubject($entity),
          ],
          [
            'icon' => 'calendar',
            'text' => $this->getRenderableDates($entity),
          ],
          [
            'icon' => 'location',
            'text' => $this->getRenderableLocation($entity),
          ],
          [
            'icon' => 'livestreaming',
            'text' => $entity->get('oe_event_online_type')->isEmpty() ? '' : $this->t('Live streaming available'),
          ],
        ],
      ],
    ];
  }

  /**
   * Get event subject as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableSubject(ContentEntityInterface $entity): array {
    return $this->contactViewBuilder->viewField($entity->get('oe_subject'), [
      'label' => 'hidden',
      'settings' => [
        'link' => FALSE,
      ],
    ]);
  }

  /**
   * Get event dates as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableDates(ContentEntityInterface $entity): array {
    return $this->contactViewBuilder->viewField($entity->get('oe_event_dates'), [
      'label' => 'hidden',
      'type' => 'daterange_default',
      'settings' => [
        'format_type' => 'oe_event_date_hour',
        'separator' => $this->t('to'),
      ],
    ]);
  }

  /**
   * Get event location as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableLocation(ContentEntityInterface $entity): array {
    // Return an empty value is no address is set.
    if ($entity->get('oe_event_venue')->isEmpty()) {
      return [];
    }

    /** @var \Drupal\oe_content_entity_venue\Entity\Venue $venue */
    $venue = $entity->get('oe_event_venue')->entity;

    // Initialize empty build array, we need this to pass cache metadata.
    $build = [];
    CacheableMetadata::createFromObject($venue)->applyTo($build);

    // If address is empty only return cache metadata, so it can bubble up.
    if ($venue->get('oe_address')->isEmpty()) {
      return $build;
    }

    // Only display locality and country, inline.
    $renderable = $this->venueViewBuilder->viewField($venue->get('oe_address'));
    $renderable[0]['locality']['#value'] .= ',&nbsp;';

    $build['locality'] = $renderable[0]['locality'];
    $build['country'] = $renderable[0]['country'];
    return $build;
  }

}
