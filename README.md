# POC Theme

Drupal 8 theme based on the [Europa Component Library](https://github.com/ec-europa/europa-component-library) (ECL).
It requires on the [POC Core](https://github.com/ec-europa/poc_core) module to be enabled on your site.

## Developer guide

Requirements:

- [Node.js](https://nodejs.org/en/): `>= 6.9.5`
- [Yarn](https://yarnpkg.com/en/): `>= 0.20.3`

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

## Components 

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

## Update ECL

Update the ECL by changing the `@ec-europa/ecl-components-preset-base` version in `package.json` and running:

```
$ npm run build
```

This will update assets such as images and fonts and re-compile CSS, resulting changes are meant to be committed to this
repository since we cannot require theme users and/or deployment procedures to build the theme locally.
