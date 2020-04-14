<?php

namespace Drupal\oe_theme_helper\PatternGenerator;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\oe_theme_helper\Event\PatternGenerationEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PatternGeneratorBase implements PatternGeneratorInterface, EntityHandlerInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * ParagraphPatternGenerator constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   */
  public function __construct(EntityTypeInterface $entityType, EventDispatcherInterface $eventDispatcher) {
    $this->eventDispatcher = $eventDispatcher;
    $this->entityType = $entityType;
  }

  /**
   * @inheritDoc
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('event_dispatcher')
    );
  }

  public function getPattern(ContentEntityInterface $entity, string $pattern, string $variant = ''): array {
    $build = [
      '#type' => 'pattern',
      '#id' => $pattern,
    ];

    if ($variant) {
      $build['#variant'] = $variant;
    }

    $build['#fields'] = $this->getFieldsForPattern($entity, $pattern, $variant);

    return $build;
  }

  protected function getFieldsForPattern(ContentEntityInterface $entity, string $pattern, string $variant = '') {
    $event = new PatternGenerationEvent($entity, $pattern, $variant);
    $this->eventDispatcher->dispatch(PatternGenerationEvent::NAME, $event);
    return $event->getFields();
  }

}
