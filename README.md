# OpenEuropa theme

[![Build Status](https://travis-ci.org/ec-europa/oe_theme.svg?branch=7-update-ecl)](https://travis-ci.org/ec-europa/oe_theme)

Drupal 8 theme based on the [Europa Component Library](https://github.com/ec-europa/europa-component-library) (ECL).
It requires the [OpenEuropa Core](https://github.com/ec-europa/oe_core) module to be enabled on your site.

## Development setup

You can build test site by running the following steps.

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
perform token substitution in test configuration files such as `behat.yml.dist`.

* Install test site by running:

```
$ ./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

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
$ docker-compose exec -u web web composer install
$ docker-compose exec -u web web ./vendor/bin/run drupal:site-setup
$ docker-compose exec -u web web ./vendor/bin/run drupal:site-install
```

Your test site will be available at [http://localhost:8080/build](http://localhost:8080/build).


## ECL Development setup

Requirements:

- [Node.js](https://nodejs.org/en/): `>= 8`
- [Yarn](https://yarnpkg.com/en/): `>= 0.27.5`

Setup your environment by running:
 
```
$ npm install
```

Build it by running:

```
$ npm run build
```

This will:

1. Compile ECL SASS in `./css/base.css` 
2. Compile local `./sass/style.scss` in `./css/style.css`
3. Copy ECL fonts in `./fonts`
4. Copy ECL images in `./images`
5. Copy ECL Twig templates in `./templates/components`

### Components 

You can use the ECL components in your Twig templates by referencing them using the [ECL Twig Loader](https://github.com/ec-europa/ecl-twig-loader)
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

Update the ECL by changing the `@ec-europa/ecl-components-preset-base` version in `package.json` and running:

```
$ npm run build
```

This will update assets such as images and fonts and re-compile CSS.
Resulting changes are meant to be committed to this repository since we cannot require theme users
and/or deployment procedures to build the theme locally.
