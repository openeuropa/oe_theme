<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_inpage_navigation\Kernel\Plugin\Condition;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\oe_theme_inpage_navigation\InPageNavigationHelper;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test InPageNavigationState plugin.
 */
class InPageNavigationStateTest extends AbstractKernelTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'filter',
    'text',
    'emr',
    'emr_node',
    'node',
    'field',
    'entity_reference_revisions',
    'oe_theme_inpage_navigation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('entity_meta');
    $this->installEntitySchema('entity_meta_relation');
    $this->installSchema('node', ['node_access']);

    $this->installConfig([
      'node',
      'filter',
      'emr',
      'emr_node',
      'oe_theme_inpage_navigation',
    ]);
    $this->createContentType(['type' => 'example', 'name' => 'Example']);
    $this->container->get('emr.installer')->installEntityMetaTypeOnContentEntityType('oe_theme_inpage_navigation', 'node', ['example']);
  }

  /**
   * Tests the inpage navigation state condition.
   *
   * @param bool|null $inpage_navigation_condition
   *   Inpage navigation state condition.
   * @param bool|null $inpage_navigation_condition_negate
   *   Is negate.
   * @param array|null $inpage_navigation_node_state
   *   Inpage navigation state in node.
   * @param array $expected
   *   Array with expected values of 'summary' and 'result'.
   *
   * @dataProvider providerTestCondition
   */
  public function testCondition(?bool $inpage_navigation_condition, ?bool $inpage_navigation_condition_negate, ?array $inpage_navigation_node_state, array $expected): void {
    $condition = $this->container->get('plugin.manager.condition')->createInstance('oe_theme_inpage_navigation_state');
    $condition->setConfiguration(['inpage_navigation_state' => $inpage_navigation_condition, 'negate' => $inpage_navigation_condition_negate]);

    if ($inpage_navigation_node_state) {
      $condition->setContextMapping([
        'node' => 'node',
      ]);
      $node = $this->createNode(['type' => 'example', 'title' => 'some title']);
      if ($inpage_navigation_node_state['inpage_navigation']) {
        InPageNavigationHelper::setInPageNavigation($node);
      }
      $contexts['node'] = EntityContext::fromEntity($node);
      $this->container->get('context.handler')->applyContextMapping($condition, $contexts);
    }

    $this->assertEqual($condition->summary(), new FormattableMarkup($expected['summary'][0], $expected['summary'][1] ?? []));
    $this->assertEqual($condition->execute(), $expected['result']);
  }

  /**
   * Data provider for testCondition().
   *
   * @return array[]
   *   The test data.
   */
  public function providerTestCondition(): array {
    return [
      'empty condition, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => NULL,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['Any inpage navigation state'],
          'result' => TRUE,
        ],
      ],
      'empty condition, negative, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => NULL,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['Any inpage navigation state'],
          'result' => FALSE,
        ],
      ],
      'empty condition, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => NULL,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['Any inpage navigation state'],
          'result' => TRUE,
        ],
      ],
      'empty condition, negative, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => NULL,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['Any inpage navigation state'],
          'result' => FALSE,
        ],
      ],
      'empty condition, no node' => [
        'inpage_navigation_condition' => NULL,
        'inpage_navigation_condition_negate' => NULL,
        'node' => NULL,
        'expected' => [
          'summary' => ['Any inpage navigation state'],
          'result' => FALSE,
        ],
      ],
      'empty condition, negative, no node' => [
        'inpage_navigation_condition' => NULL,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => NULL,
        'expected' => [
          'summary' => ['Any inpage navigation state'],
          'result' => FALSE,
        ],
      ],
      'enabled inpage navigation, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['The inpage navigation should be @state', ['@state' => 'enabled']],
          'result' => TRUE,
        ],
      ],
      'enabled inpage navigation, negative, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['The inpage navigation should not be @state', ['@state' => 'enabled']],
          'result' => FALSE,
        ],
      ],
      'enabled inpage navigation, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['The inpage navigation should be @state', ['@state' => 'enabled']],
          'result' => FALSE,
        ],
      ],
      'enabled inpage navigation, negative, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['The inpage navigation should not be @state', ['@state' => 'enabled']],
          'result' => TRUE,
        ],
      ],
      'enabled inpage navigation, no node' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => NULL,
        'expected' => [
          'summary' => ['The inpage navigation should be enabled'],
          'result' => FALSE,
        ],
      ],
      'enabled inpage navigation, negative, no node' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => NULL,
        'expected' => [
          'summary' => ['The inpage navigation should not be enabled'],
          'result' => FALSE,
        ],
      ],
      'disabled inpage navigation, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['The inpage navigation should be disabled'],
          'result' => FALSE,
        ],
      ],
      'disabled inpage navigation, negative, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['The inpage navigation should not be disabled'],
          'result' => TRUE,
        ],
      ],
      'disabled inpage navigation, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['The inpage navigation should be disabled'],
          'result' => TRUE,
        ],
      ],
      'disabled inpage navigation, negative, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['The inpage navigation should not be disabled'],
          'result' => FALSE,
        ],
      ],
      'disabled inpage navigation, no node' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => NULL,
        'expected' => [
          'summary' => ['The inpage navigation should be disabled'],
          'result' => FALSE,
        ],
      ],
      'disabled inpage navigation, negative, no node' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => NULL,
        'expected' => [
          'summary' => ['The inpage navigation should not be disabled'],
          'result' => FALSE,
        ],
      ],
    ];
  }

}
