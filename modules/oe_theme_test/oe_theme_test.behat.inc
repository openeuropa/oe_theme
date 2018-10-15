<?php

/**
 * @file
 * Contains \OeThemeTestSubContext.
 */

declare(strict_types = 1);

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\node\NodeInterface;

/**
 * Behat step definitions related to the oe_theme_test module.
 */
class OeThemeTestSubContext extends DrupalSubContextBase {

  /**
   * Creates a number of demo pages with the data provided in a table.
   *
   * @codingStandardsIgnoreStart
   * | title   | author    | status | created           |
   * | New day | Francesco | 1      | 2018-10-15 9:27am |
   * | ...     | ...       | ...    | ...               |
   * @codingStandardsIgnoreEnd
   *
   * @param \Behat\Gherkin\Node\TableNode $table
   *   The pages data.
   *
   * @Given (the following )demo page(s):
   */
  public function createDemoPages(TableNode $table): void {
    foreach ($table->getHash() as $hash) {
      $node = (object) $hash;
      $node->type = 'oe_theme_demo_page';
      $this->nodeCreate($node);
    }
  }

  /**
   * Navigates to the canonical page display of an oe_theme_demo_page node.
   *
   * @param string $title
   *   The title of the page.
   *
   * @When (I )go to the :title demo page
   * @When (I )visit the :title demo page
   */
  public function visitDemoPage(string $title): void {
    $node = $this->getDemoPageByTitle($title);
    $this->visitPath($node->toUrl()->toString());
  }

  /**
   * Retrieves a demo page node by its title.
   *
   * @todo Use the traits provided by OPENEUROPA-303 when gets in.
   *
   * @param string $title
   *   The node title.
   *
   * @return \Drupal\node\NodeInterface
   *   The node entity.
   */
  protected function getDemoPageByTitle(string $title): NodeInterface {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage->loadByProperties([
      'title' => $title,
    ]);

    if (!$nodes) {
      throw new \Exception("Could not find node with title '$title'.");
    }

    if (count($nodes) > 1) {
      throw new \Exception("Multiple nodes with title '$title' found.");
    }

    return reset($nodes);
  }

}
