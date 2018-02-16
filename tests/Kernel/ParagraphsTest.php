<?php

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\user\Entity\User;

/**
 * Class ParagraphsTests
 *
 * @package Drupal\Tests\oe_theme\Kernel
 */
class ParagraphsTest extends AbstractKernelTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'paragraphs',
    'user',
    'system',
    'file',
    'field',
    'entity_reference_revisions',
    'link',
    'text',
    'oe_paragraphs',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    $this->installSchema('system', ['sequences']);
    $this->installConfig(['oe_paragraphs']);
  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSmoke() {

    $paragraph = Paragraph::create([
      'type' => 'oe_paragraphs_links_block',
      'field_oe_paragraphs_text' => [
        "value"  =>  'Test title',
      ],
    ]);
    $paragraph->save();

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('paragraph');
    $pre_render = $view_builder->view($paragraph, 'default');

    echo $output = (string) \Drupal::service('renderer')->renderRoot($pre_render);

    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('oe_paragraphs'));
  }

}
