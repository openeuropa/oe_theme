<?php


namespace Drupal\oe_theme_helper\PatternGenerator;


use Drupal\Core\Entity\ContentEntityInterface;

interface PatternGeneratorInterface {

  public function getPattern(ContentEntityInterface $entity, string $pattern, string $variant = ''): array;

}
