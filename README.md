# OpenEuropa theme

[![Build Status](https://travis-ci.org/openeuropa/oe_theme.svg?branch=master)](https://travis-ci.org/openeuropa/oe_theme)

Drupal 8 theme based on the [Europa Component Library](https://github.com/ec-europa/europa-component-library) (ECL).

## Development setup

Requirements:

- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/en/): `>= 8`

You can build test site by running the following steps.

* Install the node dependencies:
```
$ npm install
```

* Build the artifacts:
```
$ npm run build
```

* Install all the composer dependencies:

```
$ composer install
```

* Customize build settings by copying `runner.yml.dist` to `runner.yml` and
changing relevant values.

* Setup test site by running:

```
$ ./vendor/bin/run drupal:site-setup
```

This will symlink the theme in the proper directory within the test site and
perform token substitution in test configuration files.

* Install test site by running:

```
$ ./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

## Working with Drupal 8 static caches

In order to enable and disable Twig and other static caches you can use the following Drupal Console commands:

```
$ ./vendor/bin/drupal site:mode dev  # Disable all caches.
$ ./vendor/bin/drupal site:mode prod # Enable all caches.
```

However, the user may experience a bug related to the Twig cache not being disabled.
The bug has been addressed and it is waiting to be merged. The issue can be found [here](https://github.com/hechoendrupal/drupal-console/issues/3854).
For now the twig cache should be disabled manually, by setting the cache variable to false and debug and autoload variable to true.

Step I: Create settings.local
```
  $ cp ../examples.settings.local .../default/settings.local.php 
```
uncomment the line (Disable Dynamic Page Cache):
```
settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
```
Step II: In settings.php, Load local development override configuration, by uncomment:
```
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
   include $app_root . '/' . $site_path . '/settings.local.php';
}
```
Step III: From sites root folder, create development.services.yml if does not exists and place into:
```
parameters:
  twig.config:
    debug: true
    auto_reload: true
    cache: false
```
Step IV: Clear all cache
```
$ .../drush cr

```

### Using Docker Compose

Alternatively you can build a test site using Docker and Docker-compose with the provided configuration.

Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker-compose](https://docs.docker.com/compose/)

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

## ECL components

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

### Update ECL

To update ECL components change the `@ec-europa/ecl-preset-full` version in `package.json` and run:

```
$ npm install && npm run build
```

This will update assets such as images and fonts and re-compile CSS.
Resulting changes are not meant to be committed to this repository.

### Watching and re-compiling Sass and JS changes

To watch for Sass and JS file changes - ```./sass``` folder - in order to re-compile them to the destination folder:

```
$ npm run watch
```

Resulting changes are not meant to be committed to this repository.