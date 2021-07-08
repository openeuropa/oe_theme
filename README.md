# OpenEuropa theme

[![Build Status](https://drone.fpfis.eu/api/badges/openeuropa/oe_theme/status.svg?branch=master)](https://drone.fpfis.eu/openeuropa/oe_theme)
[![Packagist](https://img.shields.io/packagist/v/openeuropa/oe_theme.svg)](https://packagist.org/packages/openeuropa/oe_theme)

Drupal 8 and 9 theme based on the [Europa Component Library][1] (ECL).

**Table of contents:**

- [Requirements](#requirements)
- [Installation](#installation)
  - [Enable the theme](#enable-the-theme)
  - [Upgrade to 3.0.0](#upgrade-to-3.0.0)
  - [Upgrade to 2.17.0](#upgrade-to-2.17.0)
  - [Upgrade to 2.15.0](#upgrade-to-2.15.0)
  - [Upgrade to 2.10.0](#upgrade-to-2.10.0)
  - [Upgrade to 2.9.0](#upgrade-to-2.9.0)
  - [Upgrade from 1.x to 2.x](#upgrade-from-1x-to-2x)
- [Companion sub-modules](#companion-sub-modules)
- [Corporate blocks](#corporate-blocks)
- [Image styles](#image-styles)
- [Development](#development)
  - [Project setup](#project-setup)
  - [Using Docker Compose](#using-docker-compose)
  - [Disable Drupal 8 caching](#disable-drupal-8-caching)
  - [Working with ECL components](#working-with-ecl-components)
- [Contributing](#contributing)
- [Versioning](#versioning)

## Requirements

This depends on the following software:

* [PHP 7.2 or 7.3](http://php.net/)

## Installation

The recommended way of installing the OpenEuropa theme is via [Composer][2].

Before proceeding, please note that theme releases are built by a continuous integration system, and include code coming
from third-party libraries, such as [ECL][1] templates and other assets. Simply Running `composer require openeuropa/oe_theme`
will download the raw theme source code, which misses required third-party code.

In order to instruct Composer to download the actual built artifact, you need to require and configure the
[Composer Artifacts][19] project. To do so run:

```
composer require openeuropa/composer-artifacts
```

Then add the following section, in your project's `composer.json`:

```
    "extra": {
        "artifacts": {
            "openeuropa/oe_theme": {
                "dist": {
                    "url": "https://github.com/{name}/releases/download/{pretty-version}/{project-name}-{pretty-version}.tar.gz",
                    "type": "tar"
                }
            }
        },
    }
```

Once you are done, run:

```bash
composer require openeuropa/oe_theme
```

This will download the fully built artifact, as opposed to the raw theme source code.

If you are not using Composer, then simply download a release artifact [here][3] (i.e. a `oe_theme-[x.y.z].tar.gz` file)
and install it as described [here][10].

### Enable the theme

In order to enable the theme in your project perform the following steps:

1. Enable the OpenEuropa Theme Helper module ```./vendor/bin/drush en oe_theme_helper```
2. Enable the OpenEuropa Theme and set it as default

```
./vendor/bin/drush theme:enable oe_theme
./vendor/bin/drush config-set system.theme default oe_theme
```

Step 1. is necessary until the following [Drupal core issue][8] is resolved. Alternatively you can patch Drupal core
with [this patch][9] and enable the theme: the patched core will then enable the required OpenEuropa Theme Helper
module.

The OpenEuropa theme supports both the **EC** and **EU** component libraries:

- Use the "European Commission" component library for European Commission websites hosted under the `ec.europa.eu` domain
- Use the "European Union" component library for European Union websites hosted under the `europa.eu` domain

The theme uses the "European Commission" component library by default, you can change that by visiting the theme setting
page.

**Note for developers**: changing the component library will only load different CSS and JS assets, the actual HTML is the
same between the two libraries.

Each component library can use one of the following ECL brandings:

- **Standardized**: standardised websites host thematic content owned by a specific DG/Agency. This is the default solution
  to host DG-specific content (policy) and is closely aligned with the core site.
- **Core**: core websites host general information shared by different websites or departments and serve as hubs for
  onward navigation to further thematic content and/or specific services. For example, the main European Commission
  website (https://ec.europa.eu) uses ECL core branding.

To learn more about EC/EU families and ECL branding visit the [ECL website](https://ec.europa.eu/component-library).

### Upgrade to 3.0.0

#### Page header pattern

ECL v3 adds the following features to the page header component:

- The possibility of displaying a thumbnail right next to the page description
- The possibility of adding a background image
- The possibility of adding an overlay on top of the background image, to compensate for poor text contrast

In order to accommodate these changes we have added the following optional fields to the "Page header" pattern:

```yaml
thumbnail:
  type: "ImageValueObject"
  label: "Thumbnail"
  description: "Thumbnail displayed alongside the description."
background_image_url:
  type: "array"
  label: "Background image URL"
  description: "Background image URL, only displayed when the theme ECL branding is set to 'Core'."
overlay:
  type: "text"
  label: "Overlay"
  description: "Optional overlay on top of background image (can be 'light', 'dark'). Only used on EC sites"
```

We have also removed the following deprecated fields:

```yaml
identity:
  type: "text"
  label: "Identity (deprecated)"
  description: "The site name. Deprecated from ECL 2.30."
infos:
  type: "array"
  label: "Infos (deprecated)"
  description: "A list of infos of the current page. Deprecated from ECL 2.30."
```

In addition, the customization that allowed the content language switcher to be rendered inside the page header
has been deprecated in favor of the new [ECL composition](https://europa-component-library.netlify.app/playground/ec/?path=/story/compositions-etranslation--default).
Instead, the OpenEuropa Content Language Switcher block provided by OpenEuropa Multilingual needs to be used
in its place. Sites updating to OpenEuropa Theme V3 are advised to place this block in the page header region
if they wish still need the functionality.

#### Content item compositions

We have removed the following compositions from our theme:

```
templates/compositions/ec-component-content-item/content-item-date.html.twig
templates/compositions/ec-component-content-item/content-item.html.twig
```

Consider using the [List item](/templates/patterns/list_item/list_item.ui_patterns.yml) pattern and its variants instead.

#### Site header

ECL v3 supports the horizontal menu in both Core and Standardised branding. In order to accommodate this, we removed the
visibility condition of the `Main navigation` block:

```yaml
visibility:
  oe_theme_helper_current_branding:
    id: oe_theme_helper_current_branding
    branding: standardised
    negate: false
    context_mapping: {  }
```

### Upgrade to 2.17.0

#### Dropdown UI pattern

In 2.17.0 we dropped support for the `Dropdown` pattern, which will be removed in the next major version.
The pattern is still available, albeit hidden on the pattern overview page. The new `Dropdown` pattern is based on the
ECL Expandable component and should work as a drop-in replacement of the old one.

### Upgrade to 2.15.0

#### Social media links pattern

In 2.15.0 we introduced a new pattern ["Social media links"](./templates/patterns/social_media_links) with two variants:

- `horizontal`: social media links will be arranged horizontally.
- `vertical`: social media links will be arranged vertically.

Therefore patterns "Social media links: horizontal" and "Social media links: vertical" are now deprecated. Use the "Social media
links" pattern with an appropriate variant instead.

### Upgrade to 2.10.0

#### ECL page header

In 2.10.0 we dropped supporting the following elements in the ["Page header" pattern](./templates/patterns/page_header/page_header.ui_patterns.yml):

- `identity`: used to show site-identifying information (such as the site name).
- `infos`: used to show secondary meta information, below the page header introduction text.

As a result, if your `PageHeaderMetadata` plugins provide such data, it will no longer be displayed.

#### ECL branding

In 2.10.0 we introduced support for ECL branding (read above for more information). The OpenEuropa Theme will use the
"Core" branding, visit the theme configuration page if you need to change that and use the "Standardised" branding instead.

To know which branding your site is supposed to use check the [ECL website](https://ec.europa.eu/component-library).

### Upgrade to 2.9.0

#### Content type teasers

If you are using the `oe_content` module together with the OpenEuropa theme then updating to 2.9.0 or later will affect your
existing teaser displays. The 2.9.0 version updates the teaser display of most content types provided by `oe_content`
so if you want to keep any customization you have made to your site you will need to redo those modifications and
override the teaser templates on your own custom theme.

#### ECL site header

In 2.9.0 we dropped support for the legacy ECL site header. To do so we had to move the language switcher block to the
`site_header_secondary` theme region. This means that:

- If your site does not use a sub-theme, then you have nothing to worry about, as we will move the block there for you
  in a post-update hook (if we find one)
- If your site does use a sub-them which displays the language switcher block, then you'll need to move it to the
  `site_header_secondary` region yourself

### Upgrade from 1.x to 2.x

- The following patterns have been removed on 2.x:
  - `dialog`
  - `file_link`
- The `variant` field on the `field` pattern has been removed. Instead, ui_patterns variants definition is used.
  Read ui_patterns [pattern definition documentation](https://ui-patterns.readthedocs.io/en/8.x-1.x/content/patterns-definition.html#pattern-definitions) for how it works.
- [OpenEuropa Corporate Blocks](https://github.com/openeuropa/oe_corporate_blocks) 1.x is not supported anymore,
  you should use version 2.x instead.

## Companion sub-modules

* [OpenEuropa Theme Contact Forms](./modules/oe_theme_contact_forms/README.md)
* [OpenEuropa Content Call for tenders companion module](./modules/oe_theme_content_call_tenders/README.md)
* [OpenEuropa Content Corporate Entity Contact companion module](./modules/oe_theme_content_entity_contact/README.md)
* [OpenEuropa Content Corporate Entity Organisation companion module](./modules/oe_theme_content_entity_organisation/README.md)
* [OpenEuropa Content Corporate Entity Venue companion module](./modules/oe_theme_content_entity_venue/README.md)
* [OpenEuropa Content Event companion module](./modules/oe_theme_content_event/README.md)
* [OpenEuropa Content News companion module](./modules/oe_theme_content_news/README.md)
* [OpenEuropa Content Page companion module](./modules/oe_theme_content_page/README.md)
* [OpenEuropa Content Policy companion module](./modules/oe_theme_content_policy/README.md)
* [OpenEuropa Content Project companion module](./modules/oe_theme_content_project/README.md)
* [OpenEuropa Content Publication companion module](./modules/oe_theme_content_publication/README.md)

## Corporate blocks

When using the theme in conjunction with the [OpenEuropa Corporate Blocks](https://github.com/openeuropa/oe_corporate_blocks)
component changing the component library will show a different footer block, namely:

- The European Commission footer, shipping with a set of links and references that must be present on all European Commission sites.
- The European Union footer, shipping with a set of links and references that must be present on all European Union sites.

## Image styles

OpenEuropa Theme ships with a number of image styles that should help users follow the guidelines set by the ECL.
The following is a list of all the vailable styles and their preferred usage:

* List item (`oe_theme_list_item`): To be used on content lists with small thumbnails.
* Featured list item (`oe_theme_list_item_featured`): To be used on highlights and content lists with big thumbnails.
* Medium (`oe_theme_medium_no_crop`): Medium sized image, part of the Main content responsive image style.
* Small (`oe_theme_small_no_crop`): Small sized image, part of the Main content responsive image style.
* Main content (`oe_theme_main_content`): Responsive image style, to be used on any image that is rendered inside
a content page.

## Development

The OpenEuropa Theme project contains all the necessary code and tools for an effective development process,
meaning:

- All PHP development dependencies (Drupal core included) are required in [composer.json](composer.json)
- All Node.js development dependencies are required in [package.json](package.json)
- Project setup and installation can be easily handled thanks to the integration with the [Task Runner][4] project.
- All system requirements are containerized using [Docker Composer][5].
- Every change to the code base will be automatically tested using [Drone][17].

Make sure you read the [developer documentation](./docs/developer-documentation.md) before starting to use the theme
in your projects.

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

**Please note:** project files and directories are symlinked within the test site by using the
[OpenEuropa Task Runner's Drupal project symlink](https://github.com/openeuropa/task-runner-drupal-project-symlink) command.

If you add a new file or directory in the root of the project, you need to re-run `drupal:site-setup` in order to make
sure they are be correctly symlinked.

If you don't want to re-run a full site setup for that, you can simply run:

```
$ ./vendor/bin/run drupal:symlink-project
```

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

### Step debugging

To enable step debugging from the command line, pass the `XDEBUG_SESSION` environment variable with any value to
the container:

```bash
docker-compose exec -e XDEBUG_SESSION=1 web <your command>
```

Please note that, starting from XDebug 3, a connection error message will be outputted in the console if the variable is
set but your client is not listening for debugging connections. The error message will cause false negatives for PHPUnit
tests.

To initiate step debugging from the browser, set the correct cookie using a browser extension or a bookmarklet
like the ones generated at https://www.jetbrains.com/phpstorm/marklets/.

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
{% include '@ecl-twig/logos' with {
  'to': 'https://ec.europa.eu',
  'title': 'European Commission',
} %}
```

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

1. Modify its source files directly in `./node_modules/@ecl-twig/[component-name]`
2. Run:

```bash
npx patch-package @ecl-twig/[component-name]
```

Or, when using Docker Compose:

```bash
docker-compose exec -u node node git config --global user.email "name@example.com"
docker-compose exec -u node node git config --global user.name "Name"
docker-compose exec -u node node npx patch-package @ecl-twig/[component-name]
```

Patches will be generated in `./patches` and applied when running `npm install`.

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct,
and the process for submitting pull requests to us.

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
[19]: https://github.com/openeuropa/composer-artifacts
