<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Paragraphs;

use Drupal\paragraphs\ParagraphInterface;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Base class for paragraphs tests.
 */
abstract class ParagraphsTestBase extends AbstractKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'language',
    'content_translation',
    'paragraphs',
    'file',
    'field',
    'entity_reference_revisions',
    'datetime',
    'link',
    'text',
    'filter',
    'options',
    'typed_link',
    'oe_paragraphs',
    'allowed_formats',
    'locale',
    'oe_multilingual',
    'oe_multilingual_demo',
    'node',
    'description_list_field',
    'oe_paragraphs_description_list',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installSchema('locale', [
      'locales_location',
      'locales_source',
      'locales_target',
    ]);
    $this->installConfig([
      'oe_paragraphs',
      'filter',
      'locale',
      'language',
      'content_translation',
      'oe_multilingual',
      'node',
      'oe_paragraphs_description_list',
      'description_list_field',
    ]);
  }

  /**
   * Render a paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   Paragraph entity.
   * @param string $langcode
   *   Rendering language code, defaults to 'en'.
   *
   * @return string
   *   Rendered output.
   *
   * @throws \Exception
   */
  protected function renderParagraph(ParagraphInterface $paragraph, string $langcode = 'en'): string {
    $render = \Drupal::entityTypeManager()
      ->getViewBuilder('paragraph')
      ->view($paragraph, 'default', $langcode);

    return $this->renderRoot($render);
  }

}
