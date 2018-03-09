<?php

namespace Drupal\oe_theme_demo\Plugin\Styleguide;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\styleguide\GeneratorInterface;
use Drupal\styleguide\Plugin\StyleguidePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Styleguide plugin for ECL components.
 *
 * @Plugin(
 *   id = "ecl_styleguide",
 *   label = @Translation("ECL Styleguide elements")
 * )
 */
class EclStyleguide extends StyleguidePluginBase {

  /**
   * The styleguide generator service.
   *
   * @var \Drupal\styleguide\GeneratorInterface
   */
  protected $generator;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CommentStyleguide.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\styleguide\GeneratorInterface $styleguide_generator
   *   Styleguide generator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GeneratorInterface $styleguide_generator, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->generator = $styleguide_generator;
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
      $container->get('styleguide.generator'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function items() {
    $items = [];

    // Prepare quote paragraph.
    $paragraph = Paragraph::create([
      'type' => 'oe_quote',
      'field_oe_text' => $this->generator->sentence(),
      'field_oe_text_long' => $this->generator->sentence(),
    ]);
    $items['ecl_quote'] = [
      'title' => $this->t('Quote'),
      'content' => $this->prepareParagraph($paragraph),
      'group' => $this->t('Paragraphs'),
    ];

    return $items;
  }

  /**
   * Render a paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   Paragraph entity.
   *
   * @return array
   *   Render array for a paragraph.
   *
   * @throws \Exception
   */
  protected function prepareParagraph(ParagraphInterface $paragraph) {
    return $this->entityTypeManager
      ->getViewBuilder('paragraph')
      ->view($paragraph, 'default');
  }

}
