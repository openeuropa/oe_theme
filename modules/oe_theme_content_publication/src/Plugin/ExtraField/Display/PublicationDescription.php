<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_publication\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays publication body and thumbnail fields.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_publication_description",
 *   label = @Translation("Publication description"),
 *   bundles = {
 *     "node.oe_publication",
 *   },
 *   visible = true
 * )
 */
class PublicationDescription extends ExtraFieldDisplayFormattedBase implements ContainerFactoryPluginInterface {

  /**
   * The entity view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * PublicationDescription constructor.
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    if ($entity->get('body')->isEmpty() && $entity->get('oe_publication_thumbnail')->isEmpty()) {
      return [];
    }

    $build = [
      '#theme' => 'oe_theme_content_publication_description',
    ];

    if (!$entity->get('body')->isEmpty()) {
      $build['#body'] = $this->viewBuilder->viewField($entity->get('body'), [
        'label' => 'hidden',
      ]);
    }

    if (!$entity->get('oe_publication_thumbnail')->isEmpty()) {
      $build['#image'] = $this->viewBuilder->viewField($entity->get('oe_publication_thumbnail'), [
        'label' => 'hidden',
        'type' => 'media_thumbnail',
        'settings' => [
          'image_style' => 'oe_theme_publication_thumbnail',
        ],
      ]);
    }

    return $build;
  }

}
