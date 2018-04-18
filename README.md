# OpenEuropa theme

[![Build Status](https://travis-ci.org/openeuropa/oe_theme.svg?branch=master)](https://travis-ci.org/openeuropa/oe_theme)

Drupal 8 theme based on the [Europa Component Library][1] (ECL).

**Table of contents:**

- [Installation](#installation)
- [Development](#development)
  - [Project setup](#project-setup)
  - [Using Docker Compose](#using-docker-compose)
  - [Disable Drupal 8 caching](#disable-drupal-8-caching)
  - [Working with ECL components](#working-with-ecl-components)

## Installation

The recommended way of installing the OpenEuropa theme is via a [Composer-based workflow][2].

In your Drupal project's main `composer.json` add the following dependency:

```json
{
    "require": { 
        "openeuropa/oe_theme": "dev-master"
    }
} 
```

And run:

```
$ composer update
```

If you are not using Composer to manage your dependencies then download the release package [here][3].

**Note:** Make sure you use an actual release and not the cloned repository as releases are built by the continuous
integration system and include code coming from third-party libraries, such as [ECL templates][1].

In order to enable the theme in your project make sure you perform the following steps:

1. Enable the OpenEuropa Theme Helper module
2. Enable the OpenEuropa Theme and set it as default

Step 1. is necessary until the following [Drupal core issue][8] is resolved.

Alternatively you can patch your Drupal core with [this patch][9] and simply enable the theme: the patched core will
enable the required OpenEuropa Theme Helper module.

## Development

The OpenEuropa Theme project contains all the necessary code and tools for an effective development process,
meaning:

- All development dependencies (Drupal core included) are required in the project's `composer.json`
- Project setup and installation can be easily handled thanks to the [Task Runner project][4] integration.
- All system requirements are containerized using [Docker Composer][5]

### Project setup

Developing the theme requires a local copy of ECL assets, including Twig templates, SASS and JavaScript source files. 

In order to fetch the required code you'll need to have [Node.js (>= 8)](https://nodejs.org/en) installed locally.


To install required Node.js dependencies run:

```
$ npm install
```

Build the artifacts:

```
$ npm run build
```

In order to download all required PHP code run:

```
$ composer install
```

This will build a fully functional Drupal site in the `./build` directory that can be used to develop and showcase the
theme.

Before setting and install the site make sure to customize default configuration values my copying `./runner.yml.dist`
to `./runner.yml` and override relevant properties.

To setup the project run:

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
- Set the OpenEuropa Theme as default theme
- Enable OpenEuropa Theme Demo and [Configuration development][6] modules

### Using Docker Compose

The setup procedure described above can be sensitively simplified by the usage of Docker Compose.

Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker-compose](https://docs.docker.com/compose)

Run:

```
$ docker-compose up -d
```

Then:

```
$ docker-compose exec -u node node npm install
$ docker-compose exec -u node node npm run build
$ docker-compose exec -u web web composer install
$ docker-compose exec -u web web ./vendor/bin/run drupal:site-setup
$ docker-compose exec -u web web ./vendor/bin/run drupal:site-install
```

Your test site will be available at [http://localhost:8080/build](http://localhost:8080/build).

Run tests as follows:

```
$ docker-compose exec -u web web ./vendor/bin/phpunit
$ docker-compose exec -u web web ./vendor/bin/behat
```

### Disable Drupal 8 caching

Manually disabling Drupal 8 caching is a laborious process that is well described [here](https://www.drupal.org/node/2598914).

Alternatively you can use the following Drupal Console command to disable/enable Drupal 8 caching:

```
$ ./vendor/bin/drupal site:mode dev  # Disable all caches.
$ ./vendor/bin/drupal site:mode prod # Enable all caches.
```

Note: to fully disable Twig caching the following additional manual steps are required:

1. Open `./build/sites/default/services.yml`
2. Set `cache: false` in `twig.config:` property.
3. Rebuild Drupal cache: `./vendor/bin/drush cr`

This is due to the following [Drupal Console issue](https://github.com/hechoendrupal/drupal-console/issues/3854).

### Working with ECL components

You can use the ECL components in your Twig templates by referencing them using the [ECL Twig Loader](https://github.com/openeuropa/ecl-twig-loader)
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

#### Update ECL

To update ECL components change the `@ec-europa/ecl-preset-full` version in `package.json` and run:

```
$ npm install && npm run build
```

This will update assets such as images and fonts and re-compile CSS.
Resulting changes are not meant to be committed to this repository.

#### Watching and re-compiling Sass and JS changes

To watch for Sass and JS file changes - ```./sass``` folder - in order to re-compile them to the destination folder:

```
$ npm run watch
```

Resulting changes are not meant to be committed to this repository.



[1]: https://github.com/ec-europa/europa-component-library
[2]: https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies#managing-contributed
[3]: https://github.com/openeuropa/oe_theme/releases
[4]: https://github.com/openeuropa/task-runner
[5]: https://docs.docker.com/compose
[6]: https://www.drupal.org/project/config_devel
[7]: https://nodejs.org/en
[8]: https://www.drupal.org/project/drupal/issues/474684
[9]: https://www.drupal.org/files/issues/474684-151.patch