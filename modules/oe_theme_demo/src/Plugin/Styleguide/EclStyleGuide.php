<?php

namespace Drupal\oe_theme_demo\Plugin\Styleguide;

use Drupal\styleguide\GeneratorInterface;
use Drupal\styleguide\Plugin\StyleguidePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Comment Styleguide items implementation.
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
   * @var \Drupal\styleguide\Generator
   */
  protected $generator;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GeneratorInterface $styleguide_generator, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->generator = $styleguide_generator;
    $this->moduleHandler = $module_handler;
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
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function items() {
    $items = [];
    if ($this->moduleHandler->moduleExists('oe_paragraphs')) {

      // Prepare quote paragraph.
      $paragraph = Paragraph::create([
        'type' => 'oe_quote',
        'field_oe_text' => 'Quote author',
        'field_oe_text_long' => 'Quote body',
      ]);
      $paragraph->save();
      $items['ecl_quote'] = [
        'title' => $this->t('Quote'),
        'content' => $this->prepareParagraph($paragraph),
        'group' => $this->t('Paragraphs'),
      ];
    }

    return $items;
  }

  /**
   * Render a paragraph.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   Paragraph entity.
   *
   * @return array
   *   Render array for a paragraph.
   *
   * @throws \Exception
   */
  protected function prepareParagraph(Paragraph $paragraph) {
    return \Drupal::entityTypeManager()
      ->getViewBuilder('paragraph')
      ->view($paragraph, 'default');

  }

}
