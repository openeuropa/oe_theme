<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the rendering of the status messages.
 */
class StatusMessagesTest extends AbstractKernelTestBase {

  /**
   * The message types available.
   *
   * Each element contains:
   *   - heading: the heading shown in the interface.
   *   - modifier: the string appended as class modifier to the wrapper of the
   *     message of a certain type.
   *
   * @var array
   */
  const MESSAGE_TYPES = [
    MessengerInterface::TYPE_STATUS => [
      'heading' => 'Status message',
      'modifier' => 'success',
    ],
    MessengerInterface::TYPE_WARNING => [
      'heading' => 'Warning message',
      'modifier' => 'warning',
    ],
    MessengerInterface::TYPE_ERROR => [
      'heading' => 'Error message',
      'modifier' => 'error',
    ],
  ];

  /**
   * Tests the rendering of the status messages.
   *
   * @param array $data
   *   An array of messages, keyed by message type.
   *
   * @throws \Exception
   *
   * @dataProvider statusMessagesProvider
   */
  public function testStatusMessages(array $data): void {
    $messenger = \Drupal::messenger();

    foreach ($data as $type => $messages) {
      foreach ($messages as $message) {
        $messenger->addMessage($message, $type);
      }
    }

    $render = ['#type' => 'status_messages'];
    $html = $this->renderRoot($render);
    $crawler = new Crawler($html);

    foreach ($data as $type => $messages) {
      $modifier = self::MESSAGE_TYPES[$type]['modifier'];
      $heading = self::MESSAGE_TYPES[$type]['heading'];

      $wrapper = $crawler->filter('div.ecl-message--' . $modifier);
      $this->assertCount(1, $wrapper, sprintf('Wrong number of wrappers found for "%s" messages.', $type));

      $title = $wrapper->filter('div.ecl-message__title');
      $this->assertCount(1, $title, sprintf('Wrong number of headings found for "%s" messages.', $type));
      $this->assertEquals($heading, trim($title->first()->text()));

      $list_items = $wrapper->filter('ul.ecl-message__body li');
      $this->assertSameSize($messages, $list_items, sprintf('Wrong number of "%s" messages found.', $type));

      foreach ($messages as $delta => $message) {
        $this->assertEquals($message, trim($list_items->eq($delta)->text()));
      }
    }

    // Verify that no message types other than the ones present in the test data
    // are rendered.
    foreach (array_diff_key(self::MESSAGE_TYPES, $data) as $type => $info) {
      $wrapper = $crawler->filter('div.ecl-message--' . $info['modifier']);
      $this->assertEmpty(
        $wrapper,
        sprintf('No messages of type "%s" were expected, but %d found.', $type, $wrapper->count())
      );
      $this->assertNotContains(
        $info['heading'],
        $html,
        'No messages of type "%s" are present, but the related heading "%s" was found.', $type, $info['heading']
      );
    }

  }

  /**
   * Data provider for status messages.
   *
   * @return array
   *   A list of test data.
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
   */
  public function statusMessagesProvider(): array {
    return [
      // A test case with no messages.
      [
        [],
      ],
      // A test case with a single "status" message.
      [
        [
          MessengerInterface::TYPE_STATUS => [
            'Status message 1.',
          ],
        ],
      ],
      // A test case with multiple "status" messages.
      [
        [
          MessengerInterface::TYPE_STATUS => [
            'Status message 1.',
            'Status message 2.',
          ],
        ],
      ],
      // A test case with a single "warning" message.
      [
        [
          MessengerInterface::TYPE_WARNING => [
            'Warning message 1.',
          ],
        ],
      ],
      // A test case with multiple "warning" messages.
      [
        [
          MessengerInterface::TYPE_WARNING => [
            'Warning message 1.',
            'Warning message 2.',
            'Warning message 3.',
          ],
        ],
      ],
      // A test case with a single "error" message.
      [
        [
          MessengerInterface::TYPE_ERROR => [
            'Error message 1.',
          ],
        ],
      ],
      // A test case with multiple "error" messages.
      [
        [
          MessengerInterface::TYPE_ERROR => [
            'Error message 1.',
            'Error message 2.',
            'Error message 3.',
            'Error message 4.',
          ],
        ],
      ],
      // A test case with two message types with one message each.
      [
        [
          MessengerInterface::TYPE_STATUS => [
            'Status message 1.',
          ],
          MessengerInterface::TYPE_WARNING => [
            'Warning message 1.',
          ],
        ],
      ],
      // A test case with two message types with multiple messages for each one.
      [
        [
          MessengerInterface::TYPE_ERROR => [
            'Error message 1.',
            'Error message 2.',
          ],
          MessengerInterface::TYPE_WARNING => [
            'Warning message 1.',
            'Warning message 2.',
            'Warning message 3.',
          ],
        ],
      ],
      // A test case with all the message types with at least one message.
      [
        [
          MessengerInterface::TYPE_STATUS => [
            'Status message 1.',
          ],
          MessengerInterface::TYPE_WARNING => [
            'Warning message 1.',
          ],
          MessengerInterface::TYPE_ERROR => [
            'Error message 1.',
            'Error message 2.',
          ],
        ],
      ],
    ];
  }

}
