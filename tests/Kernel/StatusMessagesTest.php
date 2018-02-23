<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of the status messages.
 */
class StatusMessagesTest extends AbstractKernelTest {

  /**
   * Tests the rendering of the status messages.
   *
   * @param string $type
   *   The status message type being tested.
   * @param string $heading
   *   The heading text.
   * @param string $modifier
   *   The corresponding modifier for the status type in ECL.
   *
   * @dataProvider statusMessageTypesProvider
   */
  public function testStatusMessages(string $type, string $heading, string $modifier): void {
    $messenger = \Drupal::messenger();
    $messenger->addMessage("$type message 1.", $type);
    $messenger->addMessage("$type message 2.", $type);
    $render = ['#type' => 'status_messages'];

    $html = (string) \Drupal::service('renderer')->renderRoot($render);
    $crawler = new Crawler($html);

    $wrapper = $crawler->filter('div.ecl-message--' . $modifier);
    $this->assertCount(1, $wrapper);

    $title = $wrapper->filter('div.ecl-message__title');
    $this->assertCount(1, $title);
    $this->assertEquals($heading, $title->first()->text());

    $list_items = $wrapper->filter('ul.ecl-message__body li');
    $this->assertCount(2, $list_items);

    $this->assertEquals("$type message 1.", $list_items->eq(0)->text());
    $this->assertEquals("$type message 2.", $list_items->eq(1)->text());
  }

  /**
   * Data provider for status messages types.
   *
   * @return array
   *   A list of test data.
   */
  public function statusMessageTypesProvider(): array {
    return [
      [
        MessengerInterface::TYPE_STATUS,
        'Status message',
        'success',
      ],
      [
        MessengerInterface::TYPE_WARNING,
        'Warning message',
        'warning',
      ],
      [
        MessengerInterface::TYPE_ERROR,
        'Error message',
        'error',
      ],
    ];
  }

}
