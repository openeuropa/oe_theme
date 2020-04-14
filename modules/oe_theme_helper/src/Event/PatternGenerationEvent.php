<?php

namespace Drupal\oe_theme_helper\Event;


use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Symfony\Component\EventDispatcher\Event;

class PatternGenerationEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  const NAME = 'oe_theme_helper.pattern_generation';

  protected $entity;

  protected $pattern;

  protected $variant;

  protected $fields = [];

  public function __construct($entity, $pattern, $variant) {
    $this->entity = $entity;
    $this->pattern = $pattern;
    $this->variant = $variant;
  }

  /**
   * @return mixed
   */
  public function getVariant() {
    return $this->variant;
  }

  /**
   * @param mixed $variant
   */
  public function setVariant($variant): void {
    $this->variant = $variant;
  }

  /**
   * @return mixed
   */
  public function getPattern() {
    return $this->pattern;
  }

  /**
   * @param mixed $pattern
   */
  public function setPattern($pattern): void {
    $this->pattern = $pattern;
  }

  /**
   * @return mixed
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * @param mixed $entity
   */
  public function setEntity($entity): void {
    $this->entity = $entity;
  }

  /**
   * @return mixed
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * @param mixed $fields
   */
  public function setFields($fields): void {
    $this->fields = $fields;
  }

}
