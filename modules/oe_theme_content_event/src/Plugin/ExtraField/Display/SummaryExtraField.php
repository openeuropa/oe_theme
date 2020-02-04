<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_event\EventNodeWrapper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying either the event summary or a report summary.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_summary",
 *   label = @Translation("Summary"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class SummaryExtraField extends RegistrationDateAwareExtraFieldBase {

  use StringTranslationTrait;

  /**
   * Entity view builder object.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * SummaryExtraField constructor.
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
    return $this->t('Summary');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $event = new EventNodeWrapper($entity);

    // Show description summary by default.
    $renderable = $this->viewBuilder->viewField($entity->get('oe_event_description_summary'), [
      'label' => 'hidden',
    ]);

    // If the event is over and an event report summary is available, use that.
    if ($event->isOver($this->requestDateTime) && !$entity->get('oe_event_report_summary')->isEmpty()) {
      $renderable = $this->viewBuilder->viewField($entity->get('oe_event_report_summary'), [
        'label' => 'hidden',
      ]);
    }

    $build = [
      '#theme' => 'oe_theme_content_event_summary',
      '#text' => $renderable,
    ];

    $this->applyRegistrationDateMaxAge($build, $event);
    return $build;
  }

}
