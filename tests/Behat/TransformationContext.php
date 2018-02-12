<?php

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Behat\Context\Context;

/**
 * Class TransformationContext.
 *
 * @package Drupal\Tests\nexteuropa_poetry\Behat
 */
class TransformationContext implements Context {

  /**
   * Mapping between human readable labels and CSS selectors.
   *
   * @var array
   */
  private $elements = [];

  /**
   * Mapping between human readable page names and relative URLs.
   *
   * @var array
   */
  private $pages = [];

  /**
   * TransformationContext constructor.
   *
   * @param array $elements
   *   Page elements mapping.
   * @param array $pages
   *   Page URLs mappings.
   */
  public function __construct(array $elements, array $pages) {
    $this->elements = $elements;
    $this->pages = $pages;
  }

  /**
   * Transform element label into an CSS selector, if any.
   *
   * @Transform :tag
   */
  public function transformElement($name) {
    return isset($this->elements[$name]) ? $this->elements[$name] : $name;
  }

  /**
   * Transform page label into relative URL, if any.
   *
   * @Transform /^the ([A-za-z ]+) page$/
   */
  public function transformPageLabel($name) {
    return isset($this->pages[$name]) ? $this->pages[$name] : $name;
  }

}
