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
 * Extra field displaying contacts information on events.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_contacts",
 *   label = @Translation("Contacts"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class ContactsExtraField extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Entity view builder object.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * ContactsExtraField constructor.
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
    $this->viewBuilder = $entity_type_manager->getViewBuilder('oe_contact');
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
    return $this->t('Contacts');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $cacheability = new CacheableMetadata();

    // Return an empty array if empty, so the field can be considered empty.
    if ($entity->get('oe_event_contact')->isEmpty()) {
      return [];
    }

    // We get here renderable arrays for contact entities.
    // We also divide them by bundle, so we can display them in a grid layout.
    $build = [
      '#theme' => 'oe_theme_content_event_contacts',
      '#general' => [],
      '#press' => [],
    ];
    /** @var \Drupal\Core\Field\FieldItemInterface $item */
    foreach ($entity->get('oe_event_contact') as $item) {
      if ($item->entity->bundle() === 'oe_general') {
        $build['#general'][] = $this->viewBuilder->view($item->entity);
        $cacheability->addCacheableDependency($item->entity);
      }
      if ($item->entity->bundle() === 'oe_press') {
        $build['#press'][] = $this->viewBuilder->view($item->entity);
        $cacheability->addCacheableDependency($item->entity);
      }
    }

    $cacheability->applyTo($build);
    return $build;
  }

}
