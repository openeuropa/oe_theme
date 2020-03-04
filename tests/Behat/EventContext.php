<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Mink\Element\NodeElement;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\user\Entity\Role;

/**
 * Provide steps to test the event content type display.
 */
class EventContext extends RawDrupalContext {

  /**
   * Grant all necessary permissions so that anonymous users can see events.
   *
   * @Given anonymous users can see events
   */
  public function anonymousUsersCanSeeEvents(): void {
    user_role_grant_permissions(Role::ANONYMOUS_ID, [
      'view published oe_contact',
      'view published oe_venue',
      'view published oe_organisation',
      'view published skos concept entities',
    ]);
  }

  /**
   * Assert whether the registration button is not active.
   *
   * @Then the registration button is active
   */
  public function assertRegistrationButtonActive(): void {
    $this->assertRegistrationButtonExists();
    if ($this->getRegistrationButton()->getTagName() === 'button' && $this->getRegistrationButton()->hasAttribute('disabled')) {
      throw new \Exception('The registration button was supposed to be active.');
    }
  }

  /**
   * Assert whether the registration button is not active.
   *
   * @Then the registration button is not active
   */
  public function assertRegistrationButtonNotActive(): void {
    $this->assertRegistrationButtonExists();
    if ($this->getRegistrationButton()->getTagName() === 'a') {
      throw new \Exception('The registration button was not supposed to be active.');
    }
  }

  /**
   * Assert whether the registration button exists.
   *
   * @Then the registration button should be there
   */
  public function assertRegistrationButtonExists(): void {
    if (!$this->getRegistrationButton() instanceof NodeElement) {
      throw new \Exception('The registration button was not found.');
    }
  }

  /**
   * Assert whether the registration button does not exist.
   *
   * @Then the registration button should not be there
   */
  public function assertRegistrationButtonNotExists(): void {
    if ($this->getRegistrationButton() instanceof NodeElement) {
      throw new \Exception('The registration button was found but it was not supposed to be.');
    }
  }

  /**
   * Assert that the registration block is not displayed on the page.
   *
   * @Then I should not see the registration block
   */
  public function assertRegistrationBlockNotExists() {
    $this->assertSession()->elementNotExists('css', '#event-registration-block');
  }

  /**
   * Assert that the registration block is displayed on the page.
   *
   * @Then I should see the registration block
   */
  public function assertRegistrationBlockExists() {
    $this->assertSession()->elementExists('css', '#event-registration-block');
  }

  /**
   * Get registration button, if any.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   Registration button, either a link or and actual button.
   */
  protected function getRegistrationButton(): ?NodeElement {
    // Look for registration button as a button.
    $button = $this->getSession()->getPage()->findButton('Register here');
    if ($button instanceof NodeElement) {
      return $button;
    }

    // Look for registration button as a link.
    $link = $this->getSession()->getPage()->findLink('Register here');
    if ($link instanceof NodeElement) {
      return $link;
    }

    // If none found return NULL.
    return NULL;
  }

}
