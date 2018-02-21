# OpenEuropa theme

[![Build Status](https://travis-ci.org/openeuropa/oe_theme.svg?branch=master)](https://travis-ci.org/openeuropa/oe_theme)

Drupal 8 theme based on the [Europa Component Library](https://github.com/ec-europa/europa-component-library) (ECL).

## Development setup

Requirements:

- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/en/): `>= 8`

You can build test site by running the following steps.

* Install all the composer dependencies:

```
$ composer install
```

* Install the node dependencies:
```
$ npm install
```

* Build the artifacts:
```
$ npm run build
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
