<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\views\Entity\View;

/**
 * Tests the page header block component.
 *
 * @group batch11
 */
class PageHeaderTest extends ContentRenderTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Allow visitors to register so the registration page is reachable.
    $this->config('user.settings')->set('register', 'visitors')->save();

    // Delete the frontpage view to prevent any interference.
    $view = View::load('frontpage');
    if ($view) {
      $view->delete();
      \Drupal::service('router.builder')->rebuild();
    }
  }

  /**
   * Tests the page header block component.
   */
  public function testPageHeader(): void {
    // The page header block shows the current page metadata.
    $titles = [
      'Robots are everywhere',
      'The benefits of ergonomic equipment',
    ];
    foreach ($titles as $title) {
      $node = $this->getStorage('node')->create([
        'type' => 'oe_theme_demo_page',
        'title' => $title,
        'uid' => 0,
        'status' => 1,
      ]);
      $node->save();
    }
    foreach ($titles as $title) {
      $node = $this->getNodeByTitle($title);
      $this->drupalGet($node->toUrl());
      $this->assertPageHeaderTitle($title);
      $this->assertBreadcrumbTrail(['Home']);
      $this->assertBreadcrumbActiveElement($title);
    }

    // The standard title is shown on other pages.
    $this->drupalGet('user/register');
    $this->assertPageHeaderTitle('Create new account');
    // The site identity is not shown on the standard page header.
    $this->assertSession()->elementNotExists('css', '.ecl-page-header h2.ecl-u-type-heading-2');
    $this->assertBreadcrumbTrail(['Home']);
    $this->assertBreadcrumbActiveElement('Create new account');

    // Editing the title updates the page header accordingly.
    $node = $this->getNodeByTitle('Robots are everywhere');
    $node->setTitle('Robots are everywhere nowadays')->save();
    $this->drupalGet($node->toUrl());
    $this->assertPageHeaderTitle('Robots are everywhere nowadays');
    $this->assertBreadcrumbTrail(['Home']);
    $this->assertBreadcrumbActiveElement('Robots are everywhere nowadays');

    // Content types with a summary expose it as custom metadata in the page
    // header, with the default text format applied, converting URLs to links.
    foreach (['oe_page', 'oe_policy'] as $bundle) {
      $node = $this->getStorage('node')->create([
        'type' => $bundle,
        'title' => sprintf('My %s', $bundle),
        'oe_summary' => 'http://www.example.org is a web page',
        'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/EP_PECH',
        'uid' => 0,
        'status' => 1,
      ]);
      $node->save();

      $this->drupalGet($node->toUrl());
      $intro = $this->assertSession()->elementExists('css', '.ecl-page-header .ecl-page-header__description');
      $this->assertStringContainsString('http://www.example.org is a web page', $intro->getText());
      $this->assertSession()->elementExists('named', ['link', 'http://www.example.org'], $intro);
    }
  }

  /**
   * Asserts the title shown in the page header.
   *
   * @param string $title
   *   The expected title.
   */
  protected function assertPageHeaderTitle(string $title): void {
    $heading = $this->assertSession()->elementExists('css', '.ecl-page-header .ecl-page-header__title');
    $this->assertEquals($title, trim($heading->getText()));
  }

  /**
   * Asserts the breadcrumb trail links.
   *
   * @param string[] $expected
   *   The expected breadcrumb link labels, in order.
   */
  protected function assertBreadcrumbTrail(array $expected): void {
    $breadcrumb = $this->getBreadcrumb();
    $selector = 'ol.ecl-breadcrumb__container li.ecl-breadcrumb__segment a';
    $this->assertSession()->elementsCount('css', $selector, count($expected), $breadcrumb);

    $actual = [];
    foreach ($breadcrumb->findAll('css', $selector) as $element) {
      $actual[] = trim($element->find('css', '.ecl-breadcrumb__link')->getText());
    }
    $this->assertEquals($expected, $actual);
  }

  /**
   * Asserts the active (current page) breadcrumb element.
   *
   * @param string $active_element
   *   The expected active breadcrumb label.
   */
  protected function assertBreadcrumbActiveElement(string $active_element): void {
    $breadcrumb = $this->getBreadcrumb();
    $active = $breadcrumb->find('css', 'ol.ecl-breadcrumb__container li.ecl-breadcrumb__current-page');
    $this->assertInstanceOf(NodeElement::class, $active);
    $this->assertEquals($active_element, trim($active->getText()));
  }

  /**
   * Returns the page header breadcrumb element.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The breadcrumb element.
   */
  protected function getBreadcrumb(): NodeElement {
    return $this->assertSession()->elementExists('css', 'nav.ecl-page-header__breadcrumb');
  }

}
