<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_event_event_programme\Entity\ProgrammeItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extra field displaying event Programme items as a timeline pattern.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_programme",
 *   label = @Translation("Programme"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class ProgrammeExtraField extends EventExtraFieldBase {

  use StringTranslationTrait;

  /**
   * Programme datetime format.
   */
  const PROGRAMME_DATE_TIME_FORMAT = 'oe_event_programme_date_hour';

  /**
   * Programme date format.
   */
  const PROGRAMME_DATE_FORMAT = 'oe_event_programme_date';

  /**
   * Programme time format.
   */
  const PROGRAMME_TIME_FORMAT = 'oe_event_programme_hour';


  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * RegistrationButtonExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->dateFormatter = $date_formatter;
    $this->entityRepository = $entity_repository;
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
      $container->get('date.formatter'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Programme');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    if ($entity->get('oe_event_programme')->isEmpty()) {
      return;
    }

    $cache = new CacheableMetadata();

    $build = [
      '#type' => 'pattern',
      '#id' => 'timeline',
      '#fields' => [
        'limit' => 5,
        'title' => $this->getLabel(),
        'items' => [],
      ],
    ];

    // Retrieve array of Programme item entities.
    $programmes = $entity->get('oe_event_programme')->referencedEntities();

    // Sort array of event programme items by start date's timestamp.
    usort($programmes, function ($a, $b) {
      if ($a->get('oe_event_programme_dates')->start_date->getTimestamp() === $b->get('oe_event_programme_dates')->start_date->getTimestamp()) {
        return 0;
      }
      return $a->get('oe_event_programme_dates')->start_date->getTimestamp() < $b->get('oe_event_programme_dates')->start_date->getTimestamp() ? -1 : 1;
    });

    $items = [];
    $previous_end_date = NULL;
    foreach ($programmes as $programme) {
      // Skip invalid items.
      if (!$programme instanceof ProgrammeItemInterface || $programme->get('oe_event_programme_dates')->isEmpty()) {
        continue;
      }

      $programme = $this->entityRepository->getTranslationFromContext($programme);
      $cache->addCacheableDependency($programme);

      $item = [
        'label' => $this->generateEventProgrammeLabel($programme, $previous_end_date),
        'title' => $programme->get('name')->getString(),
      ];
      if (!$programme->get('oe_description')->isEmpty()) {
        $item['body'] = [
          '#type' => 'processed_text',
          '#text' => $programme->get('oe_description')->first()->getValue()['value'],
          '#format' => $programme->get('oe_description')->first()->getValue()['format'],
          '#langcode' => $entity->language(),
        ];
      }
      $items[] = $item;

      // Keep day of previous event programme item for possible comparison.
      $previous_end_date = $this->dateFormatter->format($programme->get('oe_event_programme_dates')->end_date->getTimestamp(), 'custom', 'Ymd');
    }

    if (!$items) {
      return [];
    }

    $build['#fields']['items'] = $items;
    $cache->addCacheContexts(['timezone']);
    $cache->applyTo($build);

    return $build;
  }

  /**
   * Generate Programme item label for timeline element.
   *
   * @param \Drupal\oe_content_event_event_programme\Entity\ProgrammeItemInterface $programme
   *   Programme item entity.
   * @param string|null $previous_end_date
   *   Previous end date.
   *
   * @return array|null
   *   Render array or null.
   */
  protected function generateEventProgrammeLabel(ProgrammeItemInterface $programme, ?string $previous_end_date): ?array {
    $start_datetime = $programme->get('oe_event_programme_dates')->start_date;
    $end_datetime = $programme->get('oe_event_programme_dates')->end_date;

    $start_day = $this->dateFormatter->format($start_datetime->getTimestamp(), 'custom', 'Ymd');
    $end_day = $this->dateFormatter->format($end_datetime->getTimestamp(), 'custom', 'Ymd');
    // If event program item running in same day as previous,
    // show only start time.
    $start_date_format = $previous_end_date === $start_day ? self::PROGRAMME_TIME_FORMAT : self::PROGRAMME_DATE_TIME_FORMAT;
    // If event program item running in within day, show only end time.
    $end_date_format = $start_day === $end_day ? self::PROGRAMME_TIME_FORMAT : self::PROGRAMME_DATE_TIME_FORMAT;
    $template = '{% trans %}{{ start_date }} - {{ end_date }}{% endtrans %}';
    $context = [
      'start_date' => $this->dateFormatter->format($start_datetime->getTimestamp(), $start_date_format),
      'end_date' => $this->dateFormatter->format($end_datetime->getTimestamp(), $end_date_format),
    ];
    // If the event program item running within 1 day and there are no
    // other event program items in the current day coming before,
    // show the date with time range within a day.
    if ($start_date_format === self::PROGRAMME_DATE_TIME_FORMAT && $end_date_format === self::PROGRAMME_TIME_FORMAT) {
      $template = '{% trans %}{{ start_day }},<br>{{ start_date }} - {{ end_date }}{% endtrans %}';
      $context['start_day'] = $this->dateFormatter->format($start_datetime->getTimestamp(), self::PROGRAMME_DATE_FORMAT);
      $context['start_date'] = $this->dateFormatter->format($start_datetime->getTimestamp(), self::PROGRAMME_TIME_FORMAT);
    }

    return [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => $context,
    ];
  }

}
