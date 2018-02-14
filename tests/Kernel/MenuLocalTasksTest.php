<?php

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * Class MenuLocalTasks.
 */
class MenuLocalTasksTest extends AbstractKernelTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
  ];

  /**
   * A user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->user = User::create([
      'name' => 'username',
      'status' => 1,
    ]);
    $this->user->save();
    $this->container->get('current_user')->setAccount($this->user);
  }

  /**
   * Test menu local tasks.
   */
  public function testMenuLocalTasks() {
    $render = [
      '#theme' => 'menu_local_tasks',
      '#primary' => [
        'link1.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Active menu local task',
            'url' => Url::fromUri('http://www.active.com'),
          ],
          '#active' => TRUE,
        ],
        'link2.link' => [
          '#theme' => 'menu_local_task',
          '#link' => [
            'title' => 'Inactive menu local task',
            'url' => Url::fromUri('http://www.inactive.com'),
          ],
          '#active' => FALSE,
        ],
      ],
      '#user' => $this->user,
    ];

    $output = (string) \Drupal::service('renderer')->renderRoot($render);

    // Assert wrapper contains ECL class.
    $this->assertContains('<nav class="ecl-navigation-list-wrapper ecl-u-mb-l" >', $output);
    // Assert list contains ECL classes.
    $this->assertContains('<ul class="ecl-navigation-list ecl-navigation-list--tabs">', $output);
    // Assert active link contains  ECL classes.
    $this->assertContains('<a class="ecl-navigation-list__link ecl-navigation-list__link--active ecl-link" href="http://www.active.com">Active menu local task</a>', $output);
    // Assert regular link contains  ECL classes.
    $this->assertContains('<a class="ecl-navigation-list__link ecl-link" href="http://www.inactive.com">Inactive menu local task</a>', $output);

  }

}
