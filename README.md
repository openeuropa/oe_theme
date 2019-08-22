# OpenEuropa theme

[![Build Status](https://drone.fpfis.eu/api/badges/openeuropa/oe_theme/status.svg?branch=master)](https://drone.fpfis.eu/openeuropa/oe_theme)
[![Packagist](https://img.shields.io/packagist/v/openeuropa/oe_theme.svg)](https://packagist.org/packages/openeuropa/oe_theme)

Drupal 8 theme based on the [Europa Component Library][1] (ECL).

**Table of contents:**

- [Requirements](#requirements)
- [Installation](#installation)
- [Companion sub-modules](#companion-sub-modules)
- [Development](#development)
  - [Project setup](#project-setup)
  - [Using Docker Compose](#using-docker-compose)
  - [Disable Drupal 8 caching](#disable-drupal-8-caching)
  - [Working with ECL components](#working-with-ecl-components)
- [Contributing](#contributing)  
- [Versioning](#versioning)

## Requirements

This depends on the following software:

* [PHP 7.1](http://php.net/)

## Installation

The recommended way of installing the OpenEuropa theme is via [Composer][2].

```bash
composer require openeuropa/oe_theme
```

If you are not using Composer then download the [release package][3] and install it as described [here][10].

**Note:** Release archives are built by the continuous integration system and include code coming from third-party
libraries, such as [ECL][1] templates and other assets. Make sure you use an actual release and not the source code
archives.

### Enable the theme

In order to enable the theme in your project perform the following steps:

1. Enable the OpenEuropa Theme Helper module ```./vendor/bin/drush en oe_theme_helper```
2. Enable the OpenEuropa Theme and set it as default ```./vendor/bin/drush config-set system.theme default oe_theme```

Step 1. is necessary until the following [Drupal core issue][8] is resolved. Alternatively you can patch Drupal core
with [this patch][9] and enable the theme: the patched core will then enable the required OpenEuropa Theme Helper
module.

## Companion sub-modules

* [OpenEuropa Theme News](/modules/oe_theme_content_news/README.md)
* [OpenEuropa Theme Page](/modules/oe_theme_content_page/README.md)
* [OpenEuropa Theme Policy](/modules/oe_theme_content_policy/README.md)
* [OpenEuropa Theme Publication](/modules/oe_theme_content_publication/README.md)


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

```bash
npm install
```

To build the final artifacts run:

```bash
npm run build
```

This will compile all SASS and JavaScript files into self-contained assets that are exposed as [Drupal libraries][11].

In order to download all required PHP code run:

```bash
composer install
```

This will build a fully functional Drupal site in the `./build` directory that can be used to develop and showcase the
theme.

Before setting up and installing the site make sure to customize default configuration values by copying [runner.yml.dist](runner.yml.dist)
to `./runner.yml` and override relevant properties.

To set up the project run:

```bash
./vendor/bin/run drupal:site-setup
```

This will:

- Symlink the theme in  `./build/themes/custom/oe_theme` so that it's available to the target site
- Setup Drush and Drupal's settings using values from `./runner.yml.dist`
- Setup PHPUnit and Behat configuration files using values from `./runner.yml.dist`

After a successful setup install the site by running:

```bash
./vendor/bin/run drupal:site-install
```

This will:

- Install the target site
- Set the OpenEuropa Theme as the default theme
- Enable OpenEuropa Theme Demo and [Configuration development][6] modules

### Using Docker Compose

Alternatively, you can build a development site using [Docker](https://www.docker.com/get-docker) and 
[Docker Compose](https://docs.docker.com/compose/) with the provided configuration.

Docker provides the necessary services and tools such as a web server and a database server to get the site running, 
regardless of your local host configuration.

#### Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker Compose](https://docs.docker.com/compose/)

#### Configuration

By default, Docker Compose reads two files, a `docker-compose.yml` and an optional `docker-compose.override.yml` file.
By convention, the `docker-compose.yml` contains your base configuration and it's provided by default.
The override file, as its name implies, can contain configuration overrides for existing services or entirely new 
services.
If a service is defined in both files, Docker Compose merges the configurations.

Find more information on Docker Compose extension mechanism on [the official Docker Compose documentation](https://docs.docker.com/compose/extends/).

#### Usage

To start, run:

```bash
docker-compose up
```

It's advised to not daemonize `docker-compose` so you can turn it off (`CTRL+C`) quickly when you're done working.
However, if you'd like to daemonize it, you have to add the flag `-d`:

```bash
docker-compose up -d
```

Then:

```bash
docker-compose exec -u node node npm install
docker-compose exec -u node node npm run build
docker-compose exec web composer install
docker-compose exec web ./vendor/bin/run drupal:site-install
```

Using default configuration, the development site files should be available in the `build` directory and the development site
should be available at: [http://127.0.0.1:8080/build](http://127.0.0.1:8080/build).

#### Running the tests

To run the grumphp checks:

```bash
docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit tests:

```bash
docker-compose exec web ./vendor/bin/phpunit
```

To run the behat tests:

```bash
docker-compose exec web ./vendor/bin/behat
```

### Disable Drupal 8 caching

Manually disabling Drupal 8 caching is a laborious process that is well described [here][14].

Alternatively, you can use the following Drupal Console command to disable/enable Drupal 8 caching:

```bash
./vendor/bin/drupal site:mode dev  # Disable all caches.
./vendor/bin/drupal site:mode prod # Enable all caches.
```

Note: to fully disable Twig caching the following additional manual steps are required:

1. Open `./build/sites/default/services.yml`
2. Set the following parameters:

```yaml
parameters:
  twig.config:
    debug: true
    auto_reload: true
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

JavaScript components can be accessed by `ECL.methodName()`, e.g. `ECL.Accordion2()`.

*Important:* not all ECL templates are available to the theme for include, whenever you need include a new ECL template
remember to add it to the `copy` section of [ecl-builder.config.js](ecl-builder.config.js) and run:

```bash
npm run build
```

#### Update ECL

To update ECL components change the `@ec-europa/ecl-preset-full` version number in [package.json](package.json) and run:

```bash
npm install && npm run build
```

This will update assets such as images and fonts and re-compile CSS. Resulting changes are not meant to be committed to
this repository.

#### Watching and re-compiling Sass and JS changes

To watch for Sass and JS file changes - [/sass](/sass) folder - in order to re-compile them to the destination folder:

```bash
npm run watch
```

Resulting changes are not meant to be committed to this repository.

#### Patching ECL components

ECL components can be patched by using the [`patch-package`][18] NPM project.

To patch a component:

1. Modify its source files directly in `./node_modules/@ecl/[component-name]`
2. Run:

```bash
npx patch-package @ecl/[component-name]
```

Or, when using Docker Compose:

```bash
docker-compose exec -u node node npx patch-package @ecl/[component-name]
```

Patches will be generated in `./patches` and applied when running `npm install`.

#### Upgrade from 1.x to 2.x

Note that the following models are deprecated after upgrading from 1.x to 2.x:

- `dialog pattern`
- `file_link pattern`

Note that the following blocks are deprecated after upgrading from 1.x to 2.x:

- `Corporate Blocks Site Switcher`

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/oe_theme/tags).

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
