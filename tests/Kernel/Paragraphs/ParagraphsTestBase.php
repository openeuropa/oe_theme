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
    'user',
    'system',
    'file',
    'field',
    'entity_reference_revisions',
    'datetime',
    'image',
    'link',
    'text',
    'filter',
    'options',
    'oe_paragraphs',
    'allowed_formats',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installConfig(['oe_paragraphs', 'filter']);
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
