<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Behat;

use Behat\Gherkin\Node\TableNode;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\EuropeanUnionLanguages;
use Drupal\Tests\oe_theme\Behat\Traits\UtilityTrait;
use PHPUnit\Framework\Assert;

/**
 * Behat step definitions related to the oe_theme_test module.
 */
class OeThemeTestContext extends RawDrupalContext {

  use UtilityTrait;

  /**
   * Creates a number of demo pages with the data provided in a table.
   *
   * @codingStandardsIgnoreStart
   * | title   | author    | status | created           |
   * | New day | Francesco | 1      | 2018-10-15 9:27am |
   * | ...     | ...       | ...    | ...               |
   * @codingStandardsIgnoreEnd
   *
   * @param \Behat\Gherkin\Node\TableNode $table
   *   The pages data.
   *
   * @Given (the following )demo page(s):
   */
  public function createDemoPages(TableNode $table): void {
    foreach ($table->getHash() as $hash) {
      $node = (object) $hash;
      $node->type = 'oe_theme_demo_page';
      $this->nodeCreate($node);
    }
  }

  /**
   * Create translation for given content.
   *
   * @Given the following :language translation for the :title demo page:
   */
  public function translateDemoPage(string $language, string $title, TableNode $table): void {
    // Build translation entity.
    $values = $this->getContentValues('oe_theme_demo_page', $table);
    $language = $this->getLanguageIdByName($language);
    $translation = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create($values);

    // Add the translation to the entity.
    $entity = $this->getEntityByLabel('node', $title);
    $entity->addTranslation($language, $translation->toArray())->save();

    // Make sure URL alias is correctly generated for given translation.
    $translation = $entity->getTranslation($language);
    \Drupal::service('pathauto.generator')->createEntityAlias($translation, 'insert');
  }

  /**
   * Load an entity by label.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $label
   *   The label of the entity to load.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The loaded entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getEntityByLabel(string $entity_type_id, string $label): ContentEntityInterface {
    $manager = \Drupal::entityTypeManager();
    $label_field = $manager->getDefinition($entity_type_id)->getKey('label');
    $entity_list = $manager->getStorage($entity_type_id)->loadByProperties([$label_field => $label]);
    return array_shift($entity_list);
  }

  /**
   * Get language ID given its name.
   *
   * @param string $name
   *   Language name.
   *
   * @return string
   *   Language ID.
   */
  protected function getLanguageIdByName(string $name): string {
    foreach (\Drupal::languageManager()->getLanguages() as $language) {
      if ($language->getName() === $name) {
        return $language->getId();
      }
    }

    throw new \InvalidArgumentException("Language '{$name}' not found.");
  }

  /**
   * Return content fields array suitable for Drupal API.
   *
   * @param string $entity_type
   *   Content type.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   TableNode containing a list of fields keyed by their labels.
   *
   * @return array
   *   Content fields array.
   */
  protected function getContentValues(string $entity_type, TableNode $table): array {

    $values = ['type' => $entity_type];
    foreach ($table->getRowsHash() as $field_label => $value) {
      $name = $this->getFieldNameByLabel($entity_type, $field_label);
      $values[$name] = $value;
    }

    return $values;
  }

  /**
   * Get field name by its label.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $label
   *   Field label.
   *
   * @return string
   *   Field name.
   */
  protected function getFieldNameByLabel(string $entity_type, string $label): string {
    if ($label === 'Title') {
      return 'title';
    }

    /** @var \Drupal\Core\Field\FieldConfigBase[] $fields */
    $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $entity_type);
    foreach ($fields as $field) {
      if ($field instanceof FieldConfig && $field->label() === $label) {
        return $field->getName();
      }
    }

    throw new \InvalidArgumentException("Field '{$label}' not found.");
  }

  /**
   * Navigates to the canonical page display of a node.
   *
   * @param string $title
   *   The title of the node.
   *
   * @When (I )go to the :title page
   * @When (I )visit the :title page
   */
  public function visitPage(string $title): void {
    $node = $this->getNodeByTitle($title);
    $this->visitPath($node->toUrl()->toString());
  }

  /**
   * Navigates to the canonical page display of an oe_theme_demo_page node.
   *
   * @param string $title
   *   The title of the page.
   *
   * @When (I )go to the :title demo page
   * @When (I )visit the :title demo page
   */
  public function visitDemoPage(string $title): void {
    $node = $this->getDemoPageByTitle($title);
    $this->visitPath($node->toUrl()->toString());
  }

  /**
   * Navigates to the canonical page display of an oe_theme_demo_page node.
   *
   * @param string $language
   *   The target language.
   * @param string $title
   *   The title of the page.
   *
   * @When (I )go to the :language translation page for the :title demo page
   * @When (I )visit the :language translation page for the :title demo page
   */
  public function visitDemoPageTranslation(string $language, string $title): void {
    $node = $this->getDemoPageByTitle($title);
    $language_id = $this->getLanguageIdByName($language);
    $url = $node->toUrl('canonical', ['language' => \Drupal::languageManager()->getLanguage($language_id)]);
    $this->visitPath($url->toString());
  }

  /**
   * Creates and logs in as a user with a permission on demo pages nodes.
   *
   * @param string $action
   *   The permission action. For example "create", "edit own", "edit any",
   *   "delete any".
   *
   * @see \Drupal\DrupalExtension\Context\DrupalContext::assertLoggedInWithPermissions()
   *
   * @Given I am logged in as a user that can :action demo pages
   */
  public function givenLoggedInWithDemoPagePermission(string $action): void {
    $role = $this->getDriver()->roleCreate(["$action oe_theme_demo_page content"]);
    // Create user.
    $user = (object) [
      'name' => $this->getDriver()->getRandom()->name(8),
      'pass' => $this->getDriver()->getRandom()->name(16),
    ];
    $user->mail = "{$user->name}@example.com";
    $this->userCreate($user);

    // Assign the temporary role with given permissions.
    $this->getDriver()->userAddRole($user, $role);
    $this->roles[] = $role;

    // Login.
    $this->login($user);
  }

  /**
   * Retrieves a node by its title.
   *
   * @todo Use the traits provided by OPENEUROPA-303 when gets in.
   *
   * @param string $title
   *   The node title.
   *
   * @return \Drupal\node\NodeInterface
   *   The node entity.
   */
  protected function getNodeByTitle(string $title): NodeInterface {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage->loadByProperties([
      'title' => $title,
    ]);

    if (!$nodes) {
      throw new \Exception("Could not find node with title '$title'.");
    }

    if (count($nodes) > 1) {
      throw new \Exception("Multiple nodes with title '$title' found.");
    }

    return reset($nodes);
  }

  /**
   * Retrieves a demo page node by its title.
   *
   * @todo Use the traits provided by OPENEUROPA-303 when gets in.
   *
   * @param string $title
   *   The node title.
   *
   * @return \Drupal\node\NodeInterface
   *   The node entity.
   */
  protected function getDemoPageByTitle(string $title): NodeInterface {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage->loadByProperties([
      'title' => $title,
    ]);
    if (!$nodes) {
      throw new \Exception("Could not find node with title '$title'.");
    }
    if (count($nodes) > 1) {
      throw new \Exception("Multiple nodes with title '$title' found.");
    }
    return reset($nodes);
  }

  /**
   * Assert given corporate footer presence on page.
   *
   * @Then I should see the :component_library footer with link :link label :label image alt :img_alt title :img_title
   */
  public function assertEuropeanFooterBlockOnPage(string $component_library, string $link, string $label, string $img_alt, string $img_title): void {
    // Make sure a corporate footer is present on the page.
    $this->assertSession()->elementExists('css', 'footer.ecl-site-footer');
    // European Union footer is presented.
    Assert::assertEquals($component_library, $this->getFooterType(), 'European Union footer is not presented on the page.');

    // Assert footer data.
    $page = $this->getSession()->getPage();
    $logo_link = $page->find('css', '.ecl-site-footer__logo-link');
    Assert::assertEquals($link, $logo_link->getAttribute('href'));
    Assert::assertEquals($label, $logo_link->getAttribute('aria-label'));
    $picture = $logo_link->find('css', 'picture');
    Assert::assertEquals($img_alt, $picture->find('css', 'img')->getAttribute('alt'));
    Assert::assertEquals($img_title, $picture->getAttribute('title'));
  }

  /**
   * Assert given corporate footer absence on page.
   *
   * @Then I should not see the :component_library footer( instead)
   */
  public function assertMissingFooterBlockOnPage(string $component_library): void {
    // Make sure a corporate footer is present on the page.
    $this->assertSession()->elementExists('css', 'footer.ecl-site-footer');
    // Chosen footer has to be absent.
    Assert::assertNotEquals($component_library, $this->getFooterType());
  }

  /**
   * Set theme's corporate library value.
   *
   * @Given the theme is configured to use the :component_library style
   */
  public function setCorporateLibrary(string $component_library): void {
    $component_library_name = [
      'European Commission' => 'ec',
      'European Union' => 'eu',
    ];

    $theme_name = \Drupal::theme()->getActiveTheme()->getName();

    \Drupal::configFactory()->getEditable($theme_name . '.settings')
      ->set('component_library', $component_library_name[$component_library])->save();

    // Clears the static cache of DatabaseCacheTagsChecksum.
    // Static caches are typically cleared at the end of the request since a
    // typical web request is short lived and the process disappears when the
    // page is delivered. But if a Behat test is using DrupalContext then Drupal
    // will be bootstrapped early on (in the BeforeSuiteScope step). This starts
    // a request which is not short lived, but can live for several minutes
    // while the tests run. During the lifetime of this request there will be
    // steps executed that do requests of their own, changing the state of the
    // Drupal site. This does not however update any of the statically cached
    // data of the parent request, so this is totally unaware of the changes.
    // This causes unexpected behaviour like the failure to invalidate some
    // caches because DatabaseCacheTagsChecksum::invalidateTags() keeps a local
    // storage of which cache tags were invalidated, and this is not reset in
    // time.
    //
    // We have a step in EWCMS that does the same thing, ideally we would need
    // to port this in our traits and remove it from here.
    // @todo reuse reset check sums once available as a trait.
    \Drupal::service('cache_tags.invalidator')->resetCheckSums();
  }

  /**
   * Determines footer type.
   *
   * The ECL doesn't provide a way to determine type of the footer except as
   * to check the attributes of the logo.
   *
   * @return string
   *   'European Union' or 'European Commission'.
   */
  protected function getFooterType(): string {
    $logo = $this->getSession()->getPage()->find('css', 'footer.ecl-site-footer .ecl-site-footer__logo-link');
    if ($logo->getAttribute('aria-label') === 'Home - European Union') {
      return 'European Union';
    }
    return 'European Commission';
  }

  /**
   * Asserts that the mobile logo img tag is printed for eu component library.
   *
   * @Then the :language EU mobile logo should be available
   */
  public function assertEuMobileLogo(string $language): void {
    $lang_code = $this->getEuLanguages();
    $available_non_eu_logos = [
      'Turkish' => 'tr',
      'Icelandic' => 'is',
      'Catalan' => 'ca',
      'Arabic' => 'ar',
      'Norwegian' => 'no',
    ];
    $lang_code = array_merge($lang_code, $available_non_eu_logos);
    $langcode = $lang_code[$language] ?? 'en';
    $this->assertSession()->elementExists('css', 'img.ecl-site-header__logo-image-mobile');
    $this->assertSession()->elementAttributeContains('css', 'img.ecl-site-header__logo-image-mobile', 'src', 'oe_theme/dist/eu/images/logo/condensed-version/positive/logo-eu--' . $langcode . '.svg');
  }

  /**
   * Asserts that the header logo contains all required attributes.
   *
   * @Then the header logo should contain accessibility attributes with link :link label :label alt :alt title :title
   */
  public function assertHeaderLogoAccessibility(string $link, string $label, string $alt, string $title): void {
    $link_selector = '.ecl-site-header__logo-link';
    $logo_link = $this->getSession()->getPage()->find('css', $link_selector);

    Assert::assertEquals($link, $logo_link->getAttribute('href'));
    Assert::assertEquals($label, $logo_link->getAttribute('aria-label'));
    $img = $logo_link->find('css', 'img');
    Assert::assertEquals($alt, $img->getAttribute('alt'));
    Assert::assertEquals($title, $img->getAttribute('title'));
  }

  /**
   * Assert that the site header correlated to ECL branding theme setting.
   *
   * @param string $ecl_branding
   *   The ECL branding setting of active theme.
   *
   * @Then I should see the :ecl_branding site header
   */
  public function iShouldSeeTheSiteHeader(string $ecl_branding): void {
    if (!in_array($ecl_branding, ['Core', 'Standardised'])) {
      throw new \Exception("Theme does not support '$ecl_branding' ECL branding. The only supported brandings and 'Core' and 'Standardised'");
    }

    $this->assertSession()->elementExists('css', 'a.ecl-site-header__logo-link .ecl-site-header__logo-image');
    $this->assertSession()->elementExists('css', '.ecl-site-header__top .ecl-site-header__action .ecl-site-header__language-selector');
    $this->assertSession()->elementExists('css', '.ecl-site-header__top .ecl-site-header__action .ecl-site-header__search-container');
    $menu = $this->getSession()->getPage()->find('css', 'header.ecl-site-header .ecl-menu.ecl-menu--group1');
    if ($ecl_branding === 'Core' && !$menu) {
      $this->assertSession()->elementNotExists('css', '.ecl-site-header__banner');
    }
    else {
      $this->assertSession()->elementExists('css', '.ecl-site-header__banner');
    }
  }

  /**
   * Set theme's ECL branding setting.
   *
   * @param string $ecl_branding
   *   The ECL branding setting of active theme.
   *
   * @Given the theme is configured to use the :ecl_branding ECL branding
   */
  public function setEclBranding(string $ecl_branding): void {
    $brandings = [
      'Core' => 'core',
      'Standardised' => 'standardised',
    ];

    $theme_name = \Drupal::theme()->getActiveTheme()->getName();

    \Drupal::configFactory()->getEditable($theme_name . '.settings')
      ->set('branding', $brandings[$ecl_branding])->save();

    // Clears the static cache of DatabaseCacheTagsChecksum.
    // Static caches are typically cleared at the end of the request since a
    // typical web request is short lived and the process disappears when the
    // page is delivered. But if a Behat test is using DrupalContext then Drupal
    // will be bootstrapped early on (in the BeforeSuiteScope step). This starts
    // a request which is not short lived, but can live for several minutes
    // while the tests run. During the lifetime of this request there will be
    // steps executed that do requests of their own, changing the state of the
    // Drupal site. This does not however update any of the statically cached
    // data of the parent request, so this is totally unaware of the changes.
    // This causes unexpected behaviour like the failure to invalidate some
    // caches because DatabaseCacheTagsChecksum::invalidateTags() keeps a local
    // storage of which cache tags were invalidated, and this is not reset in
    // time.
    //
    // We have a step in EWCMS that does the same thing, ideally we would need
    // to port this in our traits and remove it from here.
    // @todo reuse reset check sums once available as a trait.
    \Drupal::service('cache_tags.invalidator')->resetCheckSums();
  }

  /**
   * Get the list of EU official language lang codes keyed by language name.
   *
   * @return array|false
   *   Array of languages keyed by language name.
   */
  protected function getEuLanguages() {
    $eu_languages = EuropeanUnionLanguages::getLanguageList();
    return array_combine(array_column($eu_languages, 0), array_column($eu_languages, 2));
  }

}
