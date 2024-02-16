<?php

declare(strict_types=1);

namespace Drupal\oe_theme_content_call_proposals\Plugin\ExtraField\Display;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display Call for proposals publication date with link.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_call_proposals_publication_info",
 *   label = @Translation("Publication information"),
 *   bundles = {
 *     "node.oe_call_proposals",
 *   },
 *   visible = true
 * )
 */
class CallForProposalsPublicationInfoExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * CallForProposalsPublicationInfoExtraField constructor.
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
   *   The date formatter service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
  public function viewElements(ContentEntityInterface $entity) {
    $date = new DrupalDateTime($entity->get('oe_publication_date')->value);

    $link = [];
    if (!$entity->get('oe_call_proposals_journal')->isEmpty()) {
      $link_value = $entity->get('oe_call_proposals_journal')->getValue();
      $link['url'] = Url::fromUri($link_value[0]['uri'])->toString();
      $link['title'] = empty($link_value[0]['title']) ? $link['url'] : $link_value[0]['title'];
    }

    return [
      '#theme' => 'oe_theme_content_call_proposals_publication_info',
      '#date' => $this->dateFormatter->format($date->getTimestamp(), 'oe_call_proposals_date_long'),
      '#link' => $link,
    ];
  }

}
