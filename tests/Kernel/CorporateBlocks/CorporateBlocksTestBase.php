<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\CorporateBlocks;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Base class for corporate block Kernel tests.
 */
abstract class CorporateBlocksTestBase extends AbstractKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'oe_corporate_blocks',
  ];

}
