<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat;

use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class EventContext for steps testing oe_event.
 */
class EventContext extends RawDrupalContext {

  /**
   * Asserting for an active registration button.
   *
   * @param string $button
   *   The button label.
   *
   * @Then I (should ) see the registration button :button inactive
   * @Then I (should ) see the :button registration button inactive
   */
  public function assertRegistrationButtonInactive(string $button): void {
    $element = $this->getSession()->getPage();
    $buttonObj = $element->findButton($button);
    if (empty($buttonObj)) {
      throw new \Exception(sprintf("The button '%s' was not found on the page %s", $button, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Asserting for inactive registration button.
   *
   * @param string $button
   *   The button label.
   *
   * @Then I (should ) see the registration button :button active
   * @Then I (should ) see the :button registration button active
   */
  public function assertRegistrationButtonActive(string $button): void {
    $element = $this->getSession()->getPage();
    $result = $element->findLink($button);

    if ($result && !$result->isVisible()) {
      throw new \Exception(sprintf("The button '%s' was not found on the page %s", $button, $this->getSession()->getCurrentUrl()));
    }

    if (empty($result)) {
      throw new \Exception(sprintf("The button '%s' was not found on the page %s", $button, $this->getSession()->getCurrentUrl()));
    }
  }

}
