<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\CorporateBlocks;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test footer block rendering.
 */
class CorporateBlocksTestBase extends AbstractKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'oe_corporate_blocks',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['oe_corporate_blocks']);
  }

}
