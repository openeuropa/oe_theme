<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\node\Entity\Node;
use Drupal\oe_content_event\EntityDecorator\Node\EventEntityDecorator;

/**
 * Extra field displaying the event registration button.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_registration_button",
 *   label = @Translation("Registration button"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class RegistrationButtonExtraField extends ExtraFieldDisplayFormattedBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    return [
      '#lazy_builder' => [RegistrationButtonExtraField::class . '::lazyBuilder', [$entity->id()]],
      '#create_placeholder' => TRUE,
    ];
  }

  /**
   * Lazy builder callback to render registration button.
   *
   * @param string|int|null $id
   *   Entity ID.
   *
   * @return array
   *   Render array.
   */
  public static function lazyBuilder($id): array {
    $current_time = \Drupal::time()->getRequestTime();
    $now = (new \DateTime())->setTimestamp($current_time);
    $event = new EventEntityDecorator(Node::load($id));
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');

    // If event has no registration information then don't display anything.
    if (!$event->hasRegistration()) {
      return [];
    }

    // Default label and description.
    $label = t('Register');
    $description = t('Register here');

    // Registration yet has to come.
    if ($event->isRegistrationPeriodYetToCome($now)) {
      $label = t('Registration will open on @datetime', [
        '@datetime' => $event->getRegistrationStartDate()->format('j F Y, H:i'),
      ]);
      $description = t('Registration is not yet opened for this event.');
    }

    // Registration period is over.
    if ($event->isRegistrationPeriodOver($now)) {
      $label = t('Registration period ended on @datetime', [
        '@datetime' => $event->getRegistrationEndDate()->format('j F Y, H:i'),
      ]);
      $description = t('Registration for this event has ended.');
    }

    // Registration period is closed.
    if ($event->isRegistrationClosed()) {
      $label = t('Registration is now closed.');
      $description = t('Registration is now closed for this event.');
    }

    $url = $view_builder->viewField($event->get('oe_event_registration_url'));
    return [
      '#theme' => 'oe_theme_content_event_registration_button',
      '#label' => $label,
      '#url' => $url[0]['#url']->toString(),
      '#description' => $description,
      '#disabled' => $event->isRegistrationClosed() || $event->isRegistrationPeriodOver($now),
    ];
  }

}
