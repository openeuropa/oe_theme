<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Functional;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Tests\BrowserTestBase;

/**
 * Test Site header rendering.
 *
 * @group batch1
 */
class SiteHeaderTest extends BrowserTestBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'language',
    'oe_search',
    'oe_theme_helper',
    'oe_multilingual',
    'menu_link_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable and set OpenEuropa Theme as default.
    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->config('system.theme')->set('default', 'oe_theme')->save();
    $this->container->set('theme.registry', NULL);

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_theme itself.
    $this->container->get('plugin.manager.ui_patterns')->clearCachedDefinitions();
    $this->configFactory = $this->container->get('config.factory');

    // Ensure that the weight of module_link_content is higher than system.
    // @see menu_link_content_install()
    module_set_weight('menu_link_content', 1);

    // Create a few menu links.
    $leaf = MenuLinkContent::create([
      'title' => 'Leaf item',
      'link' => ['uri' => 'http://leaf.eu'],
      'menu_name' => 'main',
      'expanded' => TRUE,
    ]);
    $leaf->save();

    $parent = MenuLinkContent::create([
      'title' => 'Parent item',
      'link' => ['uri' => 'http://parent.eu'],
      'menu_name' => 'main',
      'expanded' => TRUE,
    ]);
    $parent->save();

    $children = [
      'Child 1' => 'http://child-1.eu',
      'Child 2' => 'http://child-2.eu',
      'Child 3' => 'http://child-3.eu',
    ];
    foreach ($children as $title => $url) {
      $child = MenuLinkContent::create([
        'title' => $title,
        'link' => ['uri' => $url],
        'parent' => $parent->getPluginId(),
        'menu_name' => 'main',
      ]);
      $child->save();
    }
  }

  /**
   * Tests that Site header is rendered using ECL markup.
   */
  public function testSiteHeader(): void {
    $this->drupalGet('<front>');
    $assert = $this->assertSession();

    // Assert the header tag and its attribute.
    $assert->elementsCount('css', 'header.ecl-site-header', 1);
    $header = $assert->elementExists('css', 'header.ecl-site-header');
    $this->assertEquals('SiteHeader', $header->getAttribute('data-ecl-auto-init'));

    // Assert the header container.
    $header_container = $assert->elementExists('css', 'div.ecl-site-header__header div.ecl-site-header__container.ecl-container');
    $assert->elementsCount('css', 'div.ecl-site-header__header div.ecl-site-header__container.ecl-container', 1);

    // Assert the header top element.
    $header_top = $assert->elementExists('css', 'div.ecl-site-header__top', $header_container);
    $assert->elementsCount('css', 'div.ecl-site-header__top', 1, $header_container);
    $assert->elementAttributeExists('css', 'div.ecl-site-header__top', 'data-ecl-site-header-top');

    // Assert the logo element.
    $logo = $assert->elementExists('css', 'a.ecl-link.ecl-link--standalone.ecl-site-header__logo-link', $header_top);
    $this->assertEquals('https://commission.europa.eu/index_en', $logo->getAttribute('href'));
    $this->assertEquals('Home - European Commission', $logo->getAttribute('aria-label'));
    $picture = $assert->elementExists('css', 'picture.ecl-picture.ecl-site-header__picture', $logo);
    $this->assertEquals('European Commission', $picture->getAttribute('title'));
    // Desktop and mobile logo are using the same file for EC.
    $this->assertStringContainsString('oe_theme/dist/ec/images/logo/positive/logo-ec--en.svg', $picture->find('css', 'source')->getAttribute('srcset'));
    $this->assertEquals('(min-width: 996px)', $picture->find('css', 'source')->getAttribute('media'));
    $this->assertStringContainsString('oe_theme/dist/ec/images/logo/positive/logo-ec--en.svg', $picture->find('css', 'img.ecl-site-header__logo-image')->getAttribute('src'));
    $this->assertEquals('European Commission logo', $picture->find('css', 'img.ecl-site-header__logo-image')->getAttribute('alt'));

    // Assert the actionable element.
    $action = $assert->elementExists('css', 'div.ecl-site-header__action', $header_top);

    // Assert the language switcher.
    $language_switcher = $assert->elementExists('css', 'div.ecl-site-header__language', $action);

    // Assert the language switcher button.
    $language_switcher_button = $assert->elementExists('css', 'a.ecl-button.ecl-button--ghost.ecl-site-header__language-selector', $language_switcher);
    $assert->elementAttributeExists('css', 'a.ecl-site-header__language-selector', 'href');
    $assert->elementAttributeExists('css', 'a.ecl-site-header__language-selector', 'data-ecl-language-selector');
    $this->assertEquals('button', $language_switcher_button->getAttribute('role'));
    $this->assertEquals('Change language, current language is English', $language_switcher_button->getAttribute('aria-label'));
    $this->assertEquals('language-list-overlay', $language_switcher_button->getAttribute('aria-controls'));
    $icon = $language_switcher_button->find('css', "span.ecl-site-header__language-icon svg.ecl-icon.ecl-icon--s.ecl-site-header__icon[focusable='false'][aria-hidden='true']");
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#language" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $icon->getHtml());
    $this->assertEquals('en', $language_switcher_button->find('css', 'span.ecl-site-header__language-icon span.ecl-site-header__language-code')->getText());

    // Assert the language switcher container.
    $language_container = $assert->elementExists('css', 'div.ecl-site-header__language-container', $language_switcher);

    // The overlay is hidden by default.
    $assert->elementAttributeExists('css', 'div.ecl-site-header__language-container', 'hidden');
    $assert->elementAttributeExists('css', 'div.ecl-site-header__language-container', 'data-ecl-language-list-overlay');
    $this->assertEquals('language-list-overlay', $language_container->getAttribute('id'));
    $this->assertEquals('ecl-site-header__language-title', $language_container->getAttribute('aria-labelledby'));
    $this->assertEquals('dialog', $language_container->getAttribute('role'));
    // Assert the language switcher header title.
    $this->assertEquals('Select your language', $language_container->find('css', 'div.ecl-site-header__language-header div.ecl-site-header__language-title#ecl-site-header__language-title')->getText());

    // Assert the overlay close button.
    $close_button = $assert->elementExists('css', 'div.ecl-site-header__language-header button.ecl-button.ecl-button--ghost.ecl-site-header__language-close', $language_container);
    $this->assertEquals('submit', $close_button->getAttribute('type'));
    $assert->elementAttributeExists('css', 'button.ecl-site-header__language-close', 'data-ecl-language-list-close');
    $this->assertEquals('true', $close_button->find('css', 'span.ecl-button__container span.ecl-u-sr-only')->getAttribute('data-ecl-label'));
    $this->assertEquals('Close', $close_button->find('css', 'span.ecl-button__container span.ecl-u-sr-only')->getText());
    $icon = $close_button->find('css', "span.ecl-button__container svg.ecl-icon.ecl-icon--s.ecl-button__icon.ecl-button__icon--after[focusable='false'][aria-hidden='true'][data-ecl-icon]");
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#close-filled" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $icon->getHtml());

    // Assert the language switcher content.
    $eu_languages = $assert->elementExists('css', 'div.ecl-site-header__language-content div.ecl-site-header__language-category[data-ecl-language-list-eu]', $language_container);
    $this->assertCount(1, $eu_languages->findAll('css', 'ul.ecl-site-header__language-list'));
    $this->assertCount(24, $eu_languages->findAll('css', 'ul.ecl-site-header__language-list li.ecl-site-header__language-item a.ecl-link.ecl-link--standalone.ecl-site-header__language-link'));
    $this->assertCount(1, $eu_languages->findAll('css', 'ul.ecl-site-header__language-list li.ecl-site-header__language-item a.ecl-link.ecl-link--standalone.ecl-site-header__language-link.ecl-site-header__language-link--active'));

    // Assert the search container.
    $search = $assert->elementExists('css', 'div.ecl-site-header__search-container', $action);

    // Assert the search container button.
    $search_button = $assert->elementExists('css', 'a.ecl-button.ecl-button--ghost.ecl-site-header__search-toggle', $search);
    $this->assertEquals('true', $search_button->getAttribute('data-ecl-search-toggle'));
    $this->assertEquals('oe-search-search-form', $search_button->getAttribute('aria-controls'));
    $this->assertEquals('false', $search_button->getAttribute('aria-expanded'));
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#search" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $search_button->find('css', "svg.ecl-icon.ecl-icon--s[focusable='false'][aria-hidden='true']")->getHtml());
    $this->assertStringContainsString('Search', $search_button->getText());

    // Assert the search form.
    $search_form = $assert->elementExists('css', "form.ecl-search-form.ecl-site-header__search#oe-search-search-form[role='search'][method='post'][accept-charset='UTF-8'][data-ecl-search-form]", $search);
    // Assert its label and input elements.
    $this->assertEquals('Search', $search_form->find('css', "div.ecl-form-group.ecl-form-group--text-input label[for='edit-keys'].ecl-form-label.ecl-search-form__label")->getText());
    $assert->elementExists('css', "input#edit-keys.ecl-text-input.ecl-text-input--m.ecl-search-form__text-input[name='keys'][type='search']", $search_form);
    // Assert the search form button.
    $search_form_button = $assert->elementExists('css', "button.ecl-button.ecl-button--search.ecl-search-form__button[type='submit'][aria-label='Search']", $search_form);
    $this->assertEquals('Search', $search_form_button->find('css', "span.ecl-button__container span.ecl-button__label[data-ecl-label='true']")->getText());
    $icon = $search_form_button->find('css', "span.ecl-button__container svg.ecl-icon.ecl-icon--xs.ecl-button__icon.ecl-button__icon--after[focusable='false'][aria-hidden='true'][data-ecl-icon]");
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#search" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $icon->getHtml());

    // Assert the main menu block.
    $main_menu = $assert->elementExists('css', 'div#block-oe-theme-main-navigation', $header);
    $this->assertCount(1, $main_menu->findAll('css', "nav.ecl-menu.ecl-menu--group1[data-ecl-menu][data-ecl-menu-max-lines='2'][data-ecl-auto-init='Menu'][aria-expanded='false']"));
    $this->assertCount(1, $main_menu->findAll('css', "nav div.ecl-menu__overlay[data-ecl-menu-overlay]"));

    // Assert the menu container.
    $menu_container = $assert->elementExists('css', 'nav div.ecl-container.ecl-menu__container', $main_menu);
    $this->assertStringContainsString('Menu', $menu_container->find('css', "a.ecl-link.ecl-link--standalone.ecl-menu__open[href][data-ecl-menu-open]")->getText());
    $icon = $menu_container->find('css', "a.ecl-menu__open svg.ecl-icon.ecl-icon--s[focusable='false'][aria-hidden='true']");
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#hamburger" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $icon->getHtml());
    $assert->elementExists('css', "section.ecl-menu__inner[data-ecl-menu-inner]", $menu_container);
    $assert->elementExists('css', 'section header.ecl-menu__inner-header', $menu_container);
    $assert->elementExists('css', 'section header.ecl-menu__inner-header', $menu_container);
    $assert->elementExists('css', "section header button.ecl-menu__close.ecl-button.ecl-button--text[type='submit'][data-ecl-menu-close]", $menu_container);
    $assert->elementExists('css', 'section header button span.ecl-menu__close-container.ecl-button__container', $menu_container);
    $assert->elementExists('css', "section header button.ecl-menu__back.ecl-button.ecl-button--text[type='submit'][data-ecl-menu-back]", $menu_container);
    $assert->elementExists('css', "section button.ecl-button.ecl-button--ghost.ecl-menu__item.ecl-menu__items-previous[type='button'][data-ecl-menu-items-previous][tabindex='-1']", $menu_container);
    $assert->elementExists('css', "section button.ecl-button.ecl-button--ghost.ecl-menu__item.ecl-menu__items-next[type='button'][data-ecl-menu-items-next][tabindex='-1']", $menu_container);
    $assert->elementExists('css', "section button.ecl-button.ecl-button--ghost.ecl-menu__item.ecl-menu__items-next[type='button'][data-ecl-menu-items-next][tabindex='-1']", $menu_container);
    $assert->elementExists('css', "section ul.ecl-menu__list[data-ecl-menu-list]", $menu_container);
    $this->assertCount(2, $menu_container->findAll('css', 'ul.ecl-menu__list li[data-ecl-menu-item]'));
    $this->assertCount(1, $menu_container->findAll('css', 'ul.ecl-menu__list li.ecl-menu__item.ecl-menu__item--has-children'));

    // Assert the first menu item that doesn't have children.
    $first_item = $assert->elementExists('css', 'ul.ecl-menu__list li.ecl-menu__item[data-ecl-menu-item]', $menu_container);
    $this->assertEquals('Leaf item', $first_item->find('css', "a.ecl-menu__link[href='http://leaf.eu'][data-ecl-menu-link]")->getText());

    // Assert the second menu item that has children.
    $second_item = $assert->elementExists('css', 'ul.ecl-menu__list li.ecl-menu__item.ecl-menu__item--has-children[data-ecl-menu-item][data-ecl-has-children][aria-haspopup]', $menu_container);
    $this->assertEquals('Parent item', $second_item->find('css', "a.ecl-menu__link[href='http://parent.eu'][data-ecl-menu-link]")->getText());
    // Assert the button.
    $second_item_button = $assert->elementExists('css', "button.ecl-button.ecl-button--primary.ecl-menu__button-caret[type='button'][data-ecl-menu-caret]");
    $icon = $second_item_button->find('css', "span.ecl-button__container svg.ecl-icon.ecl-icon--xs.ecl-icon--rotate-180.ecl-button__icon.ecl-button__icon--after[focusable='false'][aria-hidden='true'][data-ecl-icon]");
    $this->assertEquals('<use xlink:href="/build/themes/custom/oe_theme/dist/ec/images/icons/sprites/icons.svg#corner-arrow" xmlns:xlink="http://www.w3.org/1999/xlink"></use>', $icon->getHtml());

    // Assert the children in the sublist.
    $this->assertCount(1, $second_item->findAll('css', "div.ecl-menu__mega[data-ecl-menu-mega] ul.ecl-menu__sublist"));
    $this->assertCount(3, $second_item->findAll('css', "div.ecl-menu__mega[data-ecl-menu-mega] ul.ecl-menu__sublist li.ecl-menu__subitem[data-ecl-menu-subitem]"));
    $this->assertEquals('Child 1', $second_item->find('css', "div.ecl-menu__mega ul li:nth-child(1) a.ecl-menu__sublink[href='http://child-1.eu']")->getText());
    $this->assertEquals('Child 2', $second_item->find('css', "div.ecl-menu__mega ul li:nth-child(2) a.ecl-menu__sublink[href='http://child-2.eu']")->getText());
    $this->assertEquals('Child 3', $second_item->find('css', "div.ecl-menu__mega ul li:nth-child(3) a.ecl-menu__sublink[href='http://child-3.eu']")->getText());
  }

}
