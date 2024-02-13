<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Drupal\DrupalExtension\Context\MinkContext as DrupalExtensionMinkContext;
use Drupal\Tests\oe_theme\Behat\Traits\UtilityTrait;
use PHPUnit\Framework\Assert;

/**
 * Extends default Mink context.
 */
class MinkContext extends DrupalExtensionMinkContext {

  use UtilityTrait;

  /**
   * Assert links in region.
   *
   * @param string $region
   *   Region name.
   * @param \Behat\Gherkin\Node\TableNode $links
   *   List of links.
   *
   * @throws \Exception
   *
   * @Then I should see the following links in (the ):region( region):
   */
  public function assertLinksInRegion($region, TableNode $links): void {
    $region = $this->getSession()->getPage()->find('region', $region);

    foreach ($links->getRows() as $row) {
      $result = $region->findLink($row[0]);
      if (empty($result)) {
        throw new \Exception(sprintf('No link to "%s" in the "%s" region on the page %s', $row[0], $region, $this->getSession()->getCurrentUrl()));
      }
    }
  }

  /**
   * Assert non visibility of given element.
   *
   * @Then the :element is not visible
   */
  public function assertNonVisibility($element): void {
    $node = $this->getSession()->getPage()->find('css', $element);
    $this->assertNotVisuallyVisible($node);
  }

  /**
   * Assert visibility of given element.
   *
   * @Then the :element is visible
   */
  public function assertVisibility($element): void {
    $node = $this->getSession()->getPage()->find('css', $element);
    $this->assertVisuallyVisible($node);
  }

  /**
   * Assert visibility of given overlay element.
   *
   * @Then the overlay :element is visible
   */
  public function assertOverlayVisibility($element): void {
    $node = $this->getSession()->getPage()->find('css', $element);
    Assert::assertEquals($node->getAttribute('hidden'), NULL, sprintf('Overlay "%s" is not visible, but it should be.', $element));
  }

  /**
   * Assert non visibility of given overlay element.
   *
   * @Then the overlay :element is not visible
   */
  public function assertOverlayNonVisibility($element): void {
    $this->assertSession()->elementAttributeExists('css', $element, 'hidden');
  }

  /**
   * Open language switcher dialog.
   *
   * @When I open the language switcher dialog
   */
  public function openLanguageSwitcher(): void {
    $this->getRegion('header')
      ->find('css', 'a[data-ecl-language-selector]')
      ->click();
  }

  /**
   * Assert that given language switcher link is active.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   *
   * @Then the active language switcher link in the dialog is :label
   */
  public function assertActiveLanguageSwitcherLink($label): void {
    $selector = 'li.ecl-site-header__language-item a.ecl-site-header__language-link.ecl-site-header__language-link--active';
    $overlay = $this->getLanguageSwitcherOverlay();
    $this->assertSession()->elementsCount('css', $selector, 1, $overlay);
    $this->assertSession()->elementTextContains('css', $selector, $label);
  }

  /**
   * Asserts that the breadcrumb contains a certain number of elements.
   *
   * @param string $trail
   *   Comma separated breadcrumb trail.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   *
   * @Then the breadcrumb trail should be :items
   */
  public function checkBreadcrumbTrail(string $trail): void {
    $trail_elements = explode(', ', $trail);
    $selector = 'ol.ecl-breadcrumb__container li.ecl-breadcrumb__segment a';
    $breadcrumb = $this->getBreadcrumb();

    $this->assertSession()->elementsCount('css', $selector, count($trail_elements), $breadcrumb);
    $breadcrumb_elements = $breadcrumb->findAll('css', $selector);

    $actual = [];
    foreach ($trail_elements as $key => $trail_element) {
      /** @var \Behat\Mink\Element\NodeElement $breadcrumb_element */
      $breadcrumb_element = $breadcrumb_elements[$key];
      $breadcrumb_element_link = $breadcrumb_element->find('css', '.ecl-breadcrumb__link');
      $actual[] = $breadcrumb_element_link->getText();
    }
    Assert::assertEquals($trail_elements, $actual);
  }

  /**
   * Asserts that the breadcrumb contains a certain number of elements.
   *
   * @param string $active_element
   *   The breadcrumb active element.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   *
   * @Then the breadcrumb active element should be :text
   */
  public function checkBreadcrumbActiveElement(string $active_element): void {
    $selector = 'ol.ecl-breadcrumb__container li.ecl-breadcrumb__current-page';
    $breadcrumb = $this->getBreadcrumb();
    $active_breadcrumb = $breadcrumb->find('css', $selector);
    Assert::assertEquals($active_element, $active_breadcrumb->getText());
  }

  /**
   * Get language switcher overlay element.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The language switcher overlay element.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function getLanguageSwitcherOverlay(): NodeElement {
    return $this->assertSession()
      ->elementExists('css', 'div#language-list-overlay');
  }

  /**
   * Get breadcrumb element.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The breadcrumb element.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function getBreadcrumb(): NodeElement {
    $selector = 'nav.ecl-page-header__breadcrumb';

    return $this->assertSession()->elementExists('css', $selector);
  }

  /**
   * Hover over a link.
   *
   * @When I hover over the link :link
   */
  public function iHoverOverTheLink($link): void {
    $link = $this->getSession()->getPage()->findLink($link);
    if (!$link) {
      throw new \InvalidArgumentException(sprintf('Could not not find link: "%s"', $link));
    }
    $link->mouseOver();
    // Add wait on showing menu sub-items implemented in ECL CSS:
    // https://github.com/ec-europa/europa-component-library/blob/v3-dev/src/implementations/vanilla/components/menu/_menu.scss#L850.
    $this->getSession()->wait(1000);
  }

}
