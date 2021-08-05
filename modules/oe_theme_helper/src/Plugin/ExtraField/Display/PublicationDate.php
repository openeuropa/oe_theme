<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays publication date and last update date fields.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_publication_date",
 *   label = @Translation("Publication date"),
 *   bundles = {
 *     "node.oe_publication",
 *     "node.oe_news",
 *   },
 *   visible = true
 * )
 */
class PublicationDate extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Date formats keyed by bundle ids.
   *
   * @var string[]
   */
  protected $dateFormats = [
    'oe_publication' => 'oe_theme_publication_date',
    'oe_news' => 'oe_theme_news_date',
  ];

  /**
   * Date formatter service instance.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * PublicationDate constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Publication date');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity): array {
    $bundle = $entity->bundle();
    $publication_date_timestamp = $entity->get('oe_publication_date')->date->getTimestamp();
    $publication_date = $this->dateFormatter->format($publication_date_timestamp, $this->dateFormats[$bundle]);
    if ($entity->get($bundle . '_last_updated')->isEmpty()) {
      return [
        '#markup' => $publication_date,
      ];
    }

    $last_update_timestamp = $entity->get($bundle . '_last_updated')->date->getTimestamp();
    return [
      '#type' => 'inline_template',
      '#template' => "{{ publication_date }} ({{'Last updated on: @date'|t({'@date': last_update}) }})",
      '#context' => [
        'publication_date' => $publication_date,
        'last_update' => $this->dateFormatter->format($last_update_timestamp, $this->dateFormats[$bundle]),
      ],
    ];
  }

}
