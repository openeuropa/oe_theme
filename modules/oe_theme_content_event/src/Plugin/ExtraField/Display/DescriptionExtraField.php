<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_event\EventNodeWrapper;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying the event description block.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_description",
 *   label = @Translation("Description"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class DescriptionExtraField extends RegistrationDateAwareExtraFieldBase {

  use StringTranslationTrait;

  /**
   * Entity view builder object.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * DescriptionExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity view builder object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeInterface $time, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $time);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('datetime.time'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Description');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    // Display event description using "text_featured_media" pattern.
    $build = [
      '#type' => 'pattern',
      '#id' => 'text_featured_media',
      '#fields' => [
        'title' => $this->getRenderableTitle($entity),
        'text' => $this->getRenderableText($entity),
        'caption' => $this->getRenderableFeaturedMediaLegend($entity),
      ],
    ];

    // Get media thumbnail and add media entity as cacheable dependency.
    if (!$entity->get('oe_event_featured_media')->isEmpty()) {
      $thumbnail = $entity->get('oe_event_featured_media')->entity->get('thumbnail')->first();
      $build['#fields']['image'] = ImageValueObject::fromImageItem($thumbnail);
      CacheableMetadata::createFromObject($entity->get('oe_event_featured_media')->entity)
        ->applyTo($build);
    }

    return $build;
  }

  /**
   * Get renderable section title, either 'Description' or 'Report'.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Render array.
   */
  public function getRenderableTitle(ContentEntityInterface $entity): array {
    $event = new EventNodeWrapper($entity);
    $title = t('Description');

    // If we are past the end date and an event report is available, set title.
    if ($event->isOver($this->requestDateTime) && !$entity->get('oe_event_report_text')->isEmpty()) {
      $title = t('Report');
    }

    $build = ['#markup' => $title];
    $this->applyRegistrationDateMaxAge($build, $event);
    return $build;
  }

  /**
   * Get renderable event description, either the body or the event report.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Render array.
   */
  public function getRenderableText(ContentEntityInterface $entity): array {
    $event = new EventNodeWrapper($entity);

    // By default, we show the event body field.
    // If the end date is past and event report is available, show that instead.
    $field_name = ($event->isOver($this->requestDateTime) && !$entity->get('oe_event_report_text')->isEmpty()) ? 'oe_event_report_text' : 'body';
    return $this->viewBuilder->viewField($entity->get($field_name), [
      'label' => 'hidden',
    ]);
  }

  /**
   * Get event featured media legend as a renderable array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableFeaturedMediaLegend(ContentEntityInterface $entity): array {
    return $this->viewBuilder->viewField($entity->get('oe_event_featured_media_legend'), [
      'label' => 'hidden',
    ]);
  }

}
