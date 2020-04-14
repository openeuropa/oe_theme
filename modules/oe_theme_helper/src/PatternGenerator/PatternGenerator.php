<?php

namespace Drupal\oe_theme_helper\PatternGenerator;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PatternGenerator implements PatternGeneratorInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   *
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function getPattern(ContentEntityInterface $entity, string $pattern, string $variant = ''): array {
    if (!$this->entityTypeManager->hasHandler($entity->getEntityTypeId(), 'oe_theme_pattern_generator')) {
      return [];
    }

    /** @var \Drupal\oe_theme_helper\PatternGenerator\PatternGeneratorInterface $handler */
    $handler = $this->entityTypeManager->getHandler($entity->getEntityTypeId(), 'oe_theme_pattern_generator');
    return $handler->getPattern($entity, $pattern, $variant);
  }

}
