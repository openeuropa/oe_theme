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
  protected static $modules = [
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
   */
  public function testCondition(): void {
    /** @var $condition \Drupal\Core\Condition\ConditionPluginBase */
    $condition = $this->container->get('plugin.manager.condition')->createInstance('oe_theme_inpage_navigation_state');
    $node = $this->createNode(['type' => 'example', 'title' => 'some title']);
    foreach ($this->providerTestCondition() as $test_case_name => $test_case) {
      $condition_instance = clone $condition;
      $node_instance = clone $node;
      $condition_instance->setConfiguration(['inpage_navigation_state' => $test_case['inpage_navigation_condition'], 'negate' => $test_case['inpage_navigation_condition_negate']]);
      if ($test_case['node']) {
        $condition_instance->setContextMapping([
          'node' => 'node',
        ]);
        if ($test_case['node']['inpage_navigation']) {
          InPageNavigationHelper::enableInPageNavigation($node_instance);
          $node_instance->save();
        }
        $contexts['node'] = EntityContext::fromEntity($node_instance);
        $this->container->get('context.handler')->applyContextMapping($condition_instance, $contexts);
      }
      $this->assertEqual($condition_instance->summary(), new FormattableMarkup($test_case['expected']['summary'][0], $test_case['expected']['summary'][1] ?? []), $test_case_name);
      $this->assertEqual($condition_instance->execute(), $test_case['expected']['result'], $test_case_name);
      $node_instance->get('emr_entity_metas')->delete();
    }
  }

  /**
   * Data provider for testCondition().
   *
   * @return array[]
   *   The test data.
   */
  public function providerTestCondition(): array {
    return [
      'enabled in-page navigation, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['The in-page navigation should be @state', ['@state' => 'enabled']],
          'result' => TRUE,
        ],
      ],
      'enabled in-page navigation, negative, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['The in-page navigation should not be @state', ['@state' => 'enabled']],
          'result' => FALSE,
        ],
      ],
      'enabled in-page navigation, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['The in-page navigation should be @state', ['@state' => 'enabled']],
          'result' => FALSE,
        ],
      ],
      'enabled in-page navigation, negative, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['The in-page navigation should not be @state', ['@state' => 'enabled']],
          'result' => TRUE,
        ],
      ],
      'enabled in-page navigation, no node' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => NULL,
        'expected' => [
          'summary' => ['The in-page navigation should be enabled'],
          'result' => FALSE,
        ],
      ],
      'enabled in-page navigation, negative, no node' => [
        'inpage_navigation_condition' => TRUE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => NULL,
        'expected' => [
          'summary' => ['The in-page navigation should not be enabled'],
          'result' => FALSE,
        ],
      ],
      'disabled in-page navigation, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['The in-page navigation should be disabled'],
          'result' => FALSE,
        ],
      ],
      'disabled in-page navigation, negative, node w/ active inpage navigation' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => TRUE,
        ],
        'expected' => [
          'summary' => ['The in-page navigation should not be disabled'],
          'result' => TRUE,
        ],
      ],
      'disabled in-page navigation, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['The in-page navigation should be disabled'],
          'result' => TRUE,
        ],
      ],
      'disabled in-page navigation, negative, node w/o active inpage navigation' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => [
          'inpage_navigation' => FALSE,
        ],
        'expected' => [
          'summary' => ['The in-page navigation should not be disabled'],
          'result' => FALSE,
        ],
      ],
      'disabled in-page navigation, no node' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => NULL,
        'node' => NULL,
        'expected' => [
          'summary' => ['The in-page navigation should be disabled'],
          'result' => FALSE,
        ],
      ],
      'disabled in-page navigation, negative, no node' => [
        'inpage_navigation_condition' => FALSE,
        'inpage_navigation_condition_negate' => TRUE,
        'node' => NULL,
        'expected' => [
          'summary' => ['The in-page navigation should not be disabled'],
          'result' => FALSE,
        ],
      ],
    ];
  }

}
