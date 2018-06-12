<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat\Traits;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use PHPUnit\Framework\Assert;

/**
 * Contains utility methods.
 */
trait UtilityTrait {

  /**
   * Checks that we are running on a JavaScript-enabled browser.
   *
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   *   Thrown when not running on a JS-enabled browser.
   */
  protected function assertJavaScriptEnabledBrowser(): void {
    $driver = $this->getMink()->getSession()->getDriver();
    try {
      $driver->isVisible('//body');
    }
    catch (UnsupportedDriverActionException $e) {
      // Show a helpful error message.
      throw new UnsupportedDriverActionException('This test needs to run on a real browser like Selenium or PhantomJS. Please add the "@javascript" tag to the scenario.', $driver);
    }
  }

  /**
   * Checks if an element is visible for human eyes.
   *
   * To enable certain elements to be visible for screen readers but not for
   * human eyes, Drupal provides the 'visually-hidden' class. This uses a trick
   * that fools the browser in thinking this element is still visible, even
   * though it doesn't actually show up visually in the page.
   *
   * This method will check both that the browser reports this element to be
   * visible, and that the 'visually-hidden' class is absent.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   The element to check for visibility.
   */
  protected function assertVisuallyVisible(NodeElement $element): void {
    Assert::assertTrue($this->isVisuallyVisible($element), 'The element is not visually visible but it should be');
  }

  /**
   * Checks if an element is invisible for human eyes.
   *
   * To enable certain elements to be visible for screen readers but not for
   * human eyes, Drupal provides the 'visually-hidden' class. This uses a trick
   * that fools the browser in thinking this element is still visible, even
   * though it doesn't actually show up visually in the page.
   *
   * This method will check first that the browser reports this element to be
   * invisible; if that doesn't work, then it checks if the 'visually-hidden'
   * class is present.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   The element to check for visibility.
   */
  protected function assertNotVisuallyVisible(NodeElement $element): void {
    Assert::assertFalse($this->isVisuallyVisible($element), 'The element is visually visible but it should not be');
  }

  /**
   * Returns if an element is visible for human eyes.
   *
   * To enable certain elements to be visible for screen readers but not for
   * human eyes, Drupal provides the 'visually-hidden' class. This uses a trick
   * that fools the browser in thinking this element is still visible, even
   * though it doesn't actually show up visually in the page.
   *
   * This method will check both that the browser reports this element to be
   * visible, and that the 'visually-hidden' class is absent.
   *
   * @return bool
   *   True if human optical receptors will be able to detect this particular
   *   element.
   */
  protected function isVisuallyVisible(NodeElement $element): bool {
    // This only works on JS-enabled browsers.
    $this->assertJavaScriptEnabledBrowser();

    /** @var \Behat\Mink\Driver\Selenium2Driver $driver */
    $driver = $this->getMink()->getSession()->getDriver();

    // First check if the browser reports this to be visible.
    $is_visible = $driver->isVisible($element->getXpath());

    // The "visually-hidden" class is used in Drupal to hide elements that
    // should still be visible in screen readers. This tricks the browser in
    // thinking the element is actually visible even though human eyes won't
    // find it. This class uses the "clip" css property, that will cut down the
    // visible portion of the element. This css property works only when the
    // "position" property is set either to fixed or absolute.
    $webdriver_element = $driver->getWebDriverSession()->element('xpath', $element->getXpath());
    $is_clipped = in_array($webdriver_element->css('position'), ['fixed', 'absolute']);

    return $is_visible && !$is_clipped;
  }

}
