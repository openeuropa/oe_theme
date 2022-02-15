<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\oe_content_entity_venue\Entity\VenueInterface;
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
class DetailsExtraField extends EventExtraFieldBase {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * EventExtraFieldBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Render\RendererInterface|null $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository, RendererInterface $renderer = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->entityRepository = $entity_repository;
    // Load service statically to provide backward compatibility.
    if (!$renderer instanceof RendererInterface) {
      $renderer = \Drupal::service('renderer');
    }
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('renderer')
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
    $build = [
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
        ],
      ],
    ];

    $this->addRenderableLocation($build, $entity);
    $this->addRenderableOnlineType($build, $entity);

    return $build;
  }

  /**
   * Get the event subject as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableSubject(ContentEntityInterface $entity): array {
    return $this->entityTypeManager->getViewBuilder('node')->viewField($entity->get('oe_subject'), [
      'label' => 'hidden',
      'settings' => [
        'link' => FALSE,
      ],
    ]);
  }

  /**
   * Get the event dates as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableDates(ContentEntityInterface $entity): array {
    return $this->entityTypeManager->getViewBuilder('node')->viewField($entity->get('oe_event_dates'), [
      'label' => 'hidden',
      'type' => 'daterange_timezone',
      'settings' => [
        'format_type' => 'oe_event_date_hour_timezone',
        'separator' => '-',
        'display_timezone' => FALSE,
      ],
    ]);
  }

  /**
   * Add event locality and country to event details.
   *
   * @param array $build
   *   Render array.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   */
  protected function addRenderableLocation(array &$build, ContentEntityInterface $entity): void {
    if ($entity->get('oe_event_online_only')->value) {
      $build['#fields']['items'][] = [
        'icon' => 'location',
        'text' => [
          '#markup' => $this->t('Online only'),
        ],
      ];
      return;
    }
    if (!$entity->get('oe_event_venue')->isEmpty() && $entity->get('oe_event_venue')->entity instanceof VenueInterface) {
      $address = $this->getVenueInlineAddress($entity->get('oe_event_venue')->entity);
      if (!empty($address)) {
        $build['#fields']['items'][] = [
          'icon' => 'location',
          'text' => [
            '#markup' => $address,
          ],
        ];
      }
    }
  }

  /**
   * Gets rendered address field from Venue entity.
   *
   * @param \Drupal\oe_content_entity_venue\Entity\VenueInterface $venue
   *   Venue entity.
   *
   * @return string|null
   *   Rendered address field.
   */
  protected function getVenueInlineAddress(VenueInterface $venue): ?string {
    $venue = $this->entityRepository->getTranslationFromContext($venue);
    $access = $venue->access('view', NULL, TRUE);
    if (!$access->isAllowed()) {
      return NULL;
    }

    // Bubble cacheability metadata of the entity into the current render
    // context.
    $build = $this->entityTypeManager->getViewBuilder('oe_venue')->view($venue);
    $this->renderer->render($build);

    if ($venue->get('oe_address')->isEmpty()) {
      return NULL;
    }

    $location = [];
    $renderable = $this->entityTypeManager->getViewBuilder('oe_venue')->viewField($venue->get('oe_address'));
    foreach (['locality', 'country'] as $field) {
      if (!empty($renderable[0][$field]['#value'])) {
        $location[] = $renderable[0][$field]['#value'];
      }
    }

    return implode(', ', $location);
  }

  /**
   * Add event online type to event details.
   *
   * @param array $build
   *   Render array.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   */
  protected function addRenderableOnlineType(array &$build, ContentEntityInterface $entity): void {
    if (!$entity->get('oe_event_online_type')->isEmpty()) {
      $build['#fields']['items'][] = [
        'icon' => 'livestreaming',
        'text' => $this->t('Live streaming available'),
      ];
    }
  }

}
