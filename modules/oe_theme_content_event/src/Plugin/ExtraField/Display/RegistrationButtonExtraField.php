<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_content_event\EventNodeWrapper;
use Drupal\oe_theme_helper\Cache\TimeBasedCacheTagGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying the event registration button.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_registration_button",
 *   label = @Translation("Registration button"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class RegistrationButtonExtraField extends RegistrationDateAwareExtraFieldBase {

  /**
   * Entity view builder object.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * RegistrationButtonExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   * @param \Drupal\oe_theme_helper\Cache\TimeBasedCacheTagGeneratorInterface $cache_tag_generator
   *   Time based cache tag generator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity view builder object.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeInterface $time, TimeBasedCacheTagGeneratorInterface $cache_tag_generator, EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $time, $cache_tag_generator);
    $this->viewBuilder = $entity_type_manager->getViewBuilder('node');
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
      $container->get('datetime.time'),
      $container->get('oe_theme_helper.time_based_cache_tag_generator'),
      $container->get('entity_type.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $event = new EventNodeWrapper($entity);

    // If event has no registration information then don't display anything.
    if (!$event->hasRegistration()) {
      return [];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $entity->get('oe_event_registration_url')->first();

    // Set default registration button values.
    $build = [
      '#theme' => 'oe_theme_content_event_registration_button',
      '#label' => t('Register here'),
      '#url' => $link->getUrl()->toString(),
      '#enabled' => TRUE,
    ];

    // Current request happens before the registration starts.
    if ($event->isRegistrationPeriodYetToCome($this->requestDateTime)) {
      $datetime_start = $event->getRegistrationStartDate();
      $datetime_end = $event->getRegistrationEndDate();
      $date_diff = $this->dateFormatter->formatDiff($this->requestTime, $datetime_start->getTimestamp());
      $build['#description'] = t('Registration will open in @time_left. You can register from @start_date, until @end_date.', [
        '@time_left' => $date_diff,
        '@start_date' => $this->dateFormatter->format($datetime_start->getTimestamp(), 'oe_event_date_hour'),
        '@end_date' => $this->dateFormatter->format($datetime_end->getTimestamp(), 'oe_event_date_hour'),
      ]);
      $build['#enabled'] = FALSE;

      // We invalidate this message every day at midnight.
      $this->applyMidnightTag($build, $datetime_start);

      // We invalidate this message when the registration period starts.
      $this->applyHourTag($build, $datetime_start);
      return $build;
    }

    // Current request happens within the registration period.
    if ($event->isRegistrationPeriodActive($this->requestDateTime)) {
      $datetime_start = $event->getRegistrationEndDate();
      $date_diff = $this->dateFormatter->formatDiff($this->requestTime, $datetime_start->getTimestamp(), ['granularity' => 1]);
      $build['#description'] = t('Book your seat, @time_left left to register, registration will end on @end_date', [
        '@time_left' => $date_diff,
        '@end_date' => $this->dateFormatter->format($datetime_start->getTimestamp(), 'oe_event_date_hour'),
      ]);

      // We invalidate this message every day at midnight.
      $this->applyMidnightTag($build, $datetime_start);

      // We invalidate this message when the registration period ends.
      $this->applyHourTag($build, $datetime_start);
      return $build;
    }

    // Current request happens after the registration has ended.
    if ($event->isRegistrationPeriodOver($this->requestDateTime)) {
      $datetime_start = $event->getRegistrationEndDate();
      $build['#description'] = t('Registration period ended on @date', [
        '@date' => $this->dateFormatter->format($datetime_start->getTimestamp(), 'oe_event_long_date_hour'),
      ]);
      $build['#enabled'] = FALSE;

      return $build;
    }

    return $build;
  }

}
