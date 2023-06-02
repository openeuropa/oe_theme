<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTestMul;
use Drupal\Tests\address\Kernel\Formatter\FormatterTestBase;

/**
 * Test AddressInlineFormatter plugin.
 *
 * @group batch2
 */
class AddressInlineFormatterTest extends FormatterTestBase {

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
    $this->createField('address', 'oe_theme_helper_address_inline');
  }

  /**
   * Tests formatting of address.
   */
  public function testInlineFormatterAddress(): void {
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
   * Test data for testInlineFormatterAddress.
   *
   * @return array[]
   *   An array of test data arrays with expected result.
   */
  public function addressFieldTestData(): array {
    return [
      'Brussels Belgium' => [
        'address' => [
          'country_code' => 'BE',
          'locality' => 'Brussels <Bruxelles>',
          'postal_code' => '1000',
          'address_line1' => 'Rue de la Loi, 56 <123>',
          'address_line2' => 'or \'Wetstraat\' (Dutch), meaning "Law Street"',
        ],
        'expected' => 'Rue de la Loi, 56 &lt;123&gt;, or &#039;Wetstraat&#039; (Dutch), meaning &quot;Law Street&quot;, 1000 Brussels &lt;Bruxelles&gt;, Belgium',
      ],
      'Mexico' => [
        'address' => [
          'country_code' => 'MX',
        ],
        'expected' => 'Mexico',
      ],
      'Mexico Ciudad de Mexico' => [
        'address' => [
          'country_code' => 'MX',
          'administrative_area' => 'CDMX',
        ],
        'expected' => 'CDMX, Mexico',
      ],
      'Mexico Baja California Tijuana' => [
        'address' => [
          'country_code' => 'MX',
          'administrative_area' => 'B.C.',
          'locality' => 'Tijuana',
        ],
        'expected' => 'Tijuana, B.C., Mexico',
      ],
      'Mexico Baja California Tijuana 22000' => [
        'address' => [
          'country_code' => 'MX',
          'administrative_area' => 'B.C.',
          'locality' => 'Tijuana',
          'postal_code' => '22000',
        ],
        'expected' => '22000 Tijuana, B.C., Mexico',
      ],
      'Mexico Baja California Tijuana 22000 Street' => [
        'address' => [
          'country_code' => 'MX',
          'administrative_area' => 'B.C.',
          'locality' => 'Tijuana',
          'postal_code' => '22000',
          'address_line1' => 'Street',
        ],
        'expected' => 'Street, 22000 Tijuana, B.C., Mexico',
      ],
      'Bangladesh Dhaka' => [
        'address' => [
          'country_code' => 'BD',
          'locality' => 'Dhaka',
        ],
        'expected' => 'Dhaka, Bangladesh',
      ],
      'Bangladesh Dhaka 1100' => [
        'address' => [
          'country_code' => 'BD',
          'locality' => 'Dhaka',
          'postal_code' => '1100',
        ],
        'expected' => 'Dhaka, 1100, Bangladesh',
      ],
    ];
  }

}
