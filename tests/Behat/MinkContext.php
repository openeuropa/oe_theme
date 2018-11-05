<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Mink\Element\NodeElement;
use Drupal\DrupalExtension\Context\MinkContext as DrupalExtensionMinkContext;
use Behat\Gherkin\Node\TableNode;
use Drupal\Tests\oe_theme\Behat\Traits\UtilityTrait;
use PHPUnit\Framework\Assert;

/**
 * Class MinkContext.
 */
class MinkContext extends DrupalExtensionMinkContext {

  use UtilityTrait;

  /**
   * Mapping between human readable element labels and CSS selectors.
   *
   * @var array
   */
  private $breadcrumbElements = [];

  /**
   * TransformationContext constructor.
   *
   * @param array $breadcrumb_elements
   *   Breadcrumb elements mapping.
   */
  public function __construct(array $breadcrumb_elements = []) {
    $this->breadcrumbElements = $breadcrumb_elements;
  }

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
   * Open language switcher dialog.
   *
   * @When I open the language switcher dialog
   */
  public function openLanguageSwitcher(): void {
    $this->getRegion('header')
      ->find('css', '.ecl-lang-select-sites__link')
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
    $selector = 'a.ecl-language-list__button--active';
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
    $trail_elements = explode(',', $trail);
    $selector = 'ol.ecl-breadcrumb__segments-wrapper li.ecl-breadcrumb__segment a';
    $breadcrumb = $this->getBreadcrumb();
    $this->assertSession()->elementsCount('css', $selector, count($trail_elements), $breadcrumb);
    $breadcrumb_elements = $breadcrumb->findAll('css', $selector);

    foreach ($trail_elements as $key => $trail_element) {
      /** @var \Behat\Mink\Element\NodeElement $breadcrumb_element */
      $breadcrumb_element = $breadcrumb_elements[$key];
      $value = $breadcrumb_element->find('css', 'a')->getText();
      Assert::assertEquals($trail_element, $value);
    }
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
    $selector = 'ol.ecl-breadcrumb__segments-wrapper li.ecl-breadcrumb__segment span';
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
    $selector = 'div.ecl-language-list.ecl-language-list--overlay';
    $this->assertSession()->elementExists('css', $selector);

    return $this->getSession()->getPage()->find('css', $selector);
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
    $selector = 'div.ecl-page-header nav.ecl-breadcrumb';
    $this->assertSession()->elementExists('css', $selector);

    return $this->getSession()->getPage()->find('css', $selector);
  }

  /**
   * Transform element label into an CSS selector, if any.
   *
   * @param string $label
   *   Element label.
   *
   * @return string
   *   CSS selector.
   */
  protected function transformElement($label): string {
    return isset($this->breadcrumbElements[$label]) ? $this->breadcrumbElements[$label] : $label;
  }

}
