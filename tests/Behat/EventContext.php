<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class EventContext for steps testing oe_event.
 */
class EventContext extends RawDrupalContext {

  /**
   * @Then I (should ) see the registration button :button inactive
   * @Then I (should ) see the :button registration button inactive
   */
  public function assertRegistrationButtonInactive($button)
  {
    $element = $this->getSession()->getPage();
    $buttonObj = $element->findButton($button);
    if (empty($buttonObj)) {
      throw new \Exception(sprintf("The button '%s' was not found on the page %s", $button, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * @Then I (should ) see the registration button :button active
   * @Then I (should ) see the :button registration button active
   */
  public function assertRegistrationButtonActive($button)
  {
    $element = $this->getSession()->getPage();
    $result = $element->findLink($button);

    try {
      if ($result && !$result->isVisible()) {
        throw new \Exception(sprintf("The button '%s' was not found on the page %s", $button, $this->getSession()->getCurrentUrl()));
      }
    } catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
    }

    if (empty($result)) {
      throw new \Exception(sprintf("The button '%s' was not found on the page %s", $button, $this->getSession()->getCurrentUrl()));
    }
  }

}
