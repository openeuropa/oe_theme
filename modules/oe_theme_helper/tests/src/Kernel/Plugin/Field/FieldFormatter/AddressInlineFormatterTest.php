<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\address\Kernel\Formatter\FormatterTestBase;

/**
 * Test AddressInlineFormatter plugin.
 */
class AddressInlineFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createField('address', 'oe_theme_helper_address_inline');
  }

  /**
   * Tests formatting of address.
   */
  public function testInlineFormatterAddress() {
    $entity = EntityTest::create([]);
    $entity->{$this->fieldName} = [
      'country_code' => 'BE',
      'locality' => 'Brussels',
      'postal_code' => '1000',
      'address_line1' => 'Rue de la Loi, 56',
    ];

    $this->renderEntityFields($entity, $this->display);
    $expected = 'Rue de la Loi, 56, 1000, Brussels';
    $this->assertRaw($expected);
  }

}
