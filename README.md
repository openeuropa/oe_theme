# OpenEuropa theme

[![Build Status](https://drone.fpfis.eu/api/badges/openeuropa/oe_theme/status.svg?branch=master)](https://drone.fpfis.eu/openeuropa/oe_theme)

Drupal 8 theme based on the [Europa Component Library][1] (ECL).

**Table of contents:**

- [Installation](#installation)
- [Requirements](#requirements)
- [Development](#development)
  - [Project setup](#project-setup)
  - [Using Docker Compose](#using-docker-compose)
  - [Disable Drupal 8 caching](#disable-drupal-8-caching)
  - [Working with ECL components](#working-with-ecl-components)
- [Demo module](./modules/oe_theme_demo/README.md)

## Requirements

This depends on the following software:

* [PHP 7.1](http://php.net/)

## Installation

The recommended way of installing the OpenEuropa theme is via a [Composer-based workflow][2].

In your Drupal project's main `composer.json` add the following dependency:

```json
{
    "require": { 
        "openeuropa/oe_theme": "^0.3"
    }
} 
```

And run:

```
$ composer update
```

If you are not using Composer then download the [release package][3] and install it as described [here][10].

**Note:** Release archives are built by the continuous integration system and include code coming from third-party
libraries, such as [ECL][1] templates and other assets. Make sure you use an actual release and not the source code
archives.

### Enable the theme

In order to enable the theme in your project perform the following steps:

1. Enable the OpenEuropa Theme Helper module ```./vendor/bin/drush en oe_theme_helper```
2. Enable the OpenEuropa Theme and set it as default ```./vendor/bin/drush drush config-set system.theme default oe_theme```

Step 1. is necessary until the following [Drupal core issue][8] is resolved. Alternatively you can patch Drupal core
with [this patch][9] and enable the theme: the patched core will then enable the required OpenEuropa Theme Helper
module.

## Development

The OpenEuropa Theme project contains all the necessary code and tools for an effective development process,
meaning:

- All PHP development dependencies (Drupal core included) are required in [composer.json](composer.json)
- All Node.js development dependencies are required in [package.json](package.json)
- Project setup and installation can be easily handled thanks to the integration with the [Task Runner][4] project.
- All system requirements are containerized using [Docker Composer][5].
- Every change to the code base will be automatically tested using [Drone][17].

### Project setup

Developing the theme requires a local copy of ECL assets, including Twig templates, SASS and JavaScript source files. 

In order to fetch the required code you'll need to have [Node.js (>= 8)](https://nodejs.org/en) installed locally.

To install required Node.js dependencies run:

```
$ npm install
```

To build the final artifacts run:

```
$ npm run build
```

This will compile all SASS and JavaScript files into self-contained assets that are exposed as [Drupal libraries][11].

In order to download all required PHP code run:

```
$ composer install
```

This will build a fully functional Drupal site in the `./build` directory that can be used to develop and showcase the
theme.

Before setting up and installing the site make sure to customize default configuration values by copying [runner.yml.dist](runner.yml.dist)
to `./runner.yml` and override relevant properties.

To set up the project run:

```
$ ./vendor/bin/run drupal:site-setup
```

This will:

- Symlink the theme in  `./build/themes/custom/oe_theme` so that it's available to the target site
- Setup Drush and Drupal's settings using values from `./runner.yml.dist`
- Setup PHPUnit and Behat configuration files using values from `./runner.yml.dist`

After a successful setup install the site by running:

```
$ ./vendor/bin/run drupal:site-install
```

This will:

- Install the target site
- Set the OpenEuropa Theme as the default theme
- Enable OpenEuropa Theme Demo and [Configuration development][6] modules

### Using Docker Compose

The setup procedure described above can be sensitively simplified by using Docker Compose.

Requirements:

- [Docker][12]
- [Docker-compose][13]

Run:

```
$ docker-compose up -d
```

Then:

```
$ docker-compose exec -u node node npm install
$ docker-compose exec -u node node npm run build
$ docker-compose exec web composer install
$ docker-compose exec web ./vendor/bin/run drupal:site-setup
$ docker-compose exec web ./vendor/bin/run drupal:site-install
```

Your test site will be available at [http://localhost:8080/build](http://localhost:8080/build).

Run tests as follows:

```
$ docker-compose exec web ./vendor/bin/phpunit
$ docker-compose exec web ./vendor/bin/behat
```

### Disable Drupal 8 caching

Manually disabling Drupal 8 caching is a laborious process that is well described [here][14].

Alternatively, you can use the following Drupal Console command to disable/enable Drupal 8 caching:

```
$ ./vendor/bin/drupal site:mode dev  # Disable all caches.
$ ./vendor/bin/drupal site:mode prod # Enable all caches.
```

Note: to fully disable Twig caching the following additional manual steps are required:

1. Open `./build/sites/default/services.yml`
2. Set `cache: false` in `twig.config:` property. E.g.:
```
parameters:
     twig.config:
       cache: false
 ```
3. Rebuild Drupal cache: `./vendor/bin/drush cr`

This is due to the following [Drupal Console issue][15].

### Working with ECL components

You can use the ECL components in your Twig templates by referencing them using the [ECL Twig Loader][16]
as shown below:

```twig
{% include '@ecl/logos' with {
  'to': 'https://ec.europa.eu',
  'title': 'European Commission',
} %}
```

Or:

```twig
{% include '@ec-europa/ecl-logos' with {
  'to': 'https://ec.europa.eu',
  'title': 'European Commission',
} %}
```

JavaScript components can be accessed by `ECL.methodName()`, e.g. `ECL.accordions()`.

*Important:* not all ECL templates are available to the theme for include, whenever you need include a new ECL template
remember to add it to the `copy` section of [ecl-builder.config.js](ecl-builder.config.js) and run:

```
$ npm run build
```

#### Update ECL

To update ECL components change the `@ec-europa/ecl-preset-full` version number in [package.json](package.json) and run:

```
$ npm install && npm run build
```

This will update assets such as images and fonts and re-compile CSS. Resulting changes are not meant to be committed to
this repository.

#### Watching and re-compiling Sass and JS changes

To watch for Sass and JS file changes - [/sass](/sass) folder - in order to re-compile them to the destination folder:

```
$ npm run watch
```

Resulting changes are not meant to be committed to this repository.

#### Patching ECL components

ECL components can be patched by using the [`patch-package`][18] NPM project.

To patch a component:

1. Modify its source files directly in `./node_modules/@ecl/[component-name]`
2. Run:

```
$ npx patch-package @ecl/[component-name]
```

Or, when using Docker Compose:

```
$ docker-compose exec -u node node npx patch-package @ecl/[component-name]
```

Patches will be generated in `./patches` and applied when running `npm install`.

[1]: https://github.com/ec-europa/europa-component-library
[2]: https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies#managing-contributed
[3]: https://github.com/openeuropa/oe_theme/releases
[4]: https://github.com/openeuropa/task-runner
[5]: https://docs.docker.com/compose
[6]: https://www.drupal.org/project/config_devel
[7]: https://nodejs.org/en
[8]: https://www.drupal.org/project/drupal/issues/474684
[9]: https://www.drupal.org/files/issues/474684-151.patch
[10]: https://www.drupal.org/docs/8/extending-drupal-8/installing-themes
[11]: https://www.drupal.org/docs/8/theming-drupal-8/adding-stylesheets-css-and-javascript-js-to-a-drupal-8-theme
[12]: https://www.docker.com/get-docker
[13]: https://docs.docker.com/compose
[14]: https://www.drupal.org/node/2598914
[15]: https://github.com/hechoendrupal/drupal-console/issues/3854
[16]: https://github.com/openeuropa/ecl-twig-loader
[17]: https://drone.io
[18]: https://www.npmjs.com/package/patch-package
