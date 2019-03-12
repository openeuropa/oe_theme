# OpenEuropa Theme demo

The theme ships with a demo module which provides all necessary configuration and code needed to showcase the theme's
most important features.

The demo module includes:

- A custom main menu with sub-menu items
- An overview page for all Drupal-related components called "Style guide"
- Placeholder blocks like:
 - Language switcher
 - Site switcher

### Requirements

* [drupal/styleguide ~1.0-alpha3](https://www.drupal.org/project/styleguide)

## Installation

* Install its dependencies:

```bash
$ composer require drupal/styleguide
```

## Usage

### OpenEuropa Theme demo

In order to enable the OpenEuropa Theme Demo module follow the instruction [here][1] or enable it via [Drush][2]
by running:

```
$ ./vendor/bin/drush en oe_theme_demo -y
```

[1]: https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
[2]: https://www.drush.org/
