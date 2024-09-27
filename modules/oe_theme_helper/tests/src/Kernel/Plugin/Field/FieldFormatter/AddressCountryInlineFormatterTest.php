<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\Tests\address\Kernel\Formatter\FormatterTestBase;
use Drupal\entity_test\Entity\EntityTestMul;

/**
 * Test AddressCountryInlineFormatter plugin.
 *
 * @group batch2
 */
class AddressCountryInlineFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createField('address', 'oe_theme_helper_address_country_inline');
  }

  /**
   * Tests formatting of address inline with countries only.
   */
  public function testInlineFormatterAddressCountry(): void {
    $entity = EntityTestMul::create([]);
    foreach ($this->addressFieldTestData() as $data) {
      $cloned_entity = clone $entity;
      $cloned_entity->{$this->fieldName} = $data['address'];
      $this->renderEntityFields($cloned_entity, $this->display);
      $this->assertRaw($data['expected']);
      unset($cloned_entity);
    }
  }

  /**
   * Test data for testInlineFormatterAddressCountry.
   *
   * @return array[]
   *   An array of test data arrays with expected result.
   */
  public function addressFieldTestData(): array {
    return [
      'Single item' => [
        'address' => [
          [
            'country_code' => 'MX',
          ],
        ],
        'expected' => 'Mexico',
      ],
      'Multiple items' => [
        'address' => [
          [
            'country_code' => 'BE',
            'locality' => 'Bruges',
          ],
          [
            'country_code' => 'MX',
          ],
          [
            'country_code' => 'BD',
            'locality' => 'Dhaka',
            'postal_code' => '1100',
          ],
          [
            'country_code' => 'BE',
            'locality' => 'Brussels <Bruxelles>',
            'postal_code' => '1000',
            'address_line1' => 'Rue de la Loi, 56 <123>',
            'address_line2' => 'or \'Wetstraat\' (Dutch), meaning "Law Street"',
          ],
        ],
        'expected' => 'Belgium, Mexico, Bangladesh',
      ],
    ];
  }

}
