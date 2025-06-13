<?php

declare(strict_types=1);

namespace Drupal\oe_theme_helper\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_paragraphs\Event\FlagOptionsEvent;
use Drupal\oe_theme_helper\WebtoolsIconsProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to alter the list of allowed flag icons.
 */
class FlagOptionsEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The webtools icons provider.
   *
   * @var \Drupal\oe_theme_helper\WebtoolsIconsProviderInterface
   */
  protected $webtoolsIconsProvider;

  /**
   * Constructs a new FlagOptionsEventSubscriber object.
   *
   * @param \Drupal\oe_theme_helper\WebtoolsIconsProviderInterface $webtoolsIconsProvider
   *   The webtools icons provider service.
   */
  public function __construct(WebtoolsIconsProviderInterface $webtoolsIconsProvider) {
    $this->webtoolsIconsProvider = $webtoolsIconsProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FlagOptionsEvent::class => 'alterFlagOptions',
    ];
  }

  /**
   * Alter the list of allowed flag icons.
   *
   * @param \Drupal\oe_paragraphs\Event\FlagOptionsEvent $event
   *   The event.
   */
  public function alterFlagOptions(FlagOptionsEvent $event): void {
    $event->setFlagOptions($this->webtoolsIconsProvider->getAllowedIconValues(['flags']));
  }

}
