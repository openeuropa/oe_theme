<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_content_event\EventNodeWrapper;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying livestream information on events.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_livestream",
 *   label = @Translation("Livestream"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class LivestreamExtraField extends DateAwareExtraFieldBase {

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * LivestreamExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface $cache_tag_generator
   *   Time based cache tag generator service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TimeInterface $time, TimeBasedCacheTagGeneratorInterface $cache_tag_generator, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $time, $cache_tag_generator);
    $this->dateFormatter = $date_formatter;
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
      $container->get('datetime.time'),
      $container->get('oe_time_caching.time_based_cache_tag_generator'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Livestream');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $build = ['#theme' => 'oe_theme_content_event_livestream'];
    $event = EventNodeWrapper::getInstance($entity);

    // All the online fields have to be filled to show online information.
    if (!$event->hasOnlineType() || !$event->hasOnlineLink() || !$event->hasOnlineDates()) {
      $this->isEmpty = TRUE;
      return $build;
    }
    // If the livestream is over, we don't display any info.
    if ($event->isOnlinePeriodOver($this->requestDateTime)) {
      $this->isEmpty = TRUE;
      return $build;
    }
    // If the livestream didn't start yet, we cache it by its start date and
    // render the date only.
    if ($event->isOnlinePeriodYetToCome($this->requestDateTime)) {
      $this->applyHourTag($build, $event->getOnlineStartDate());
    }
    $build['#date'] = $this->t('Starts on @date', [
      '@date' => $this->dateFormatter->format($event->getOnlineStartDate()->getTimestamp(), 'oe_event_long_date_hour'),
    ]);
    // If the livestream is ongoing, we add the livestream link.
    if ($event->isOnlinePeriodActive($this->requestDateTime)) {
      $link = $entity->get('oe_event_online_link')->first();
      $value = $link->getValue();
      $build += [
        '#url' => $link->getUrl(),
        '#label' => $value['title'],
      ];
    }

    return $build;
  }

}
