<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_organisation\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\oe_content_entity_contact\Entity\ContactInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Display organisation details.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_organisation_teaser_details",
 *   label = @Translation("Teaser details"),
 *   bundles = {
 *     "node.oe_organisation",
 *   },
 *   visible = true
 * )
 */
class TeaserDetailsExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * TeaserDetailsExtraField constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
  public function viewElements(ContentEntityInterface $entity) {
    // Return an empty array if empty, so the field can be considered empty.
    if ($entity->get('oe_organisation_contact')->isEmpty()) {
      return [];
    }

    $contact = $entity->get('oe_organisation_contact')->entity;
    if (!$contact instanceof ContactInterface) {
      return [];
    }

    $build = [
      '#type' => 'pattern',
      '#id' => 'field_list',
      '#variant' => 'horizontal',
      '#fields' => [
        'items' => [],
      ],
    ];
    $cache = CacheableMetadata::createFromRenderArray($build);

    $contact_access = $contact->access('view', NULL, TRUE);
    $cache->addCacheableDependency($contact);
    $cache->addCacheableDependency($contact_access);

    if (!$contact_access->isAllowed()) {
      $cache->applyTo($build);
      return $build;
    }

    $items = [];
    $fields = [
      'oe_website' => [],
      'oe_email' => ['type' => 'email_mailto'],
      'oe_phone' => [],
      'oe_address' => [
        'type' => 'oe_theme_helper_address_inline',
        'settings' => ['delimiter' => ', '],
      ],
    ];
    foreach ($fields as $field_name => $display_options) {
      if (!$contact->get($field_name)->isEmpty()) {
        $items[] = $this->getRenderableFieldListItem($contact, $field_name, $display_options);
      }
    }
    $build['#fields']['items'] = $items;
    $cache->applyTo($build);

    return $build;
  }

  /**
   * Get renderable item for field list pattern.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Content entity.
   * @param string $field_name
   *   Field name.
   * @param array $display_options
   *   Display options for field rendering.
   *
   * @return array
   *   Renderable array.
   */
  protected function getRenderableFieldListItem(ContentEntityInterface $entity, string $field_name, array $display_options = []): array {
    $display_options += [
      'label' => 'hidden',
    ];
    $renderable = $this->entityTypeManager->getViewBuilder('oe_contact')
      ->viewField($entity->get($field_name), $display_options);

    return [
      'label' => $renderable['#title'],
      'body' => $renderable,
    ];
  }

}
