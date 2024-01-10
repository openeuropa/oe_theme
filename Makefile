include .env

# ecl: build ECL from dev branch indicated in .env file.
.PHONY: ecl
ecl: .env build-ecl copy-dist copy-twig compile-sass

# Files to make
.env:
	cp .env.dist .env

## build-ecl: build ECL.
.PHONY: build-ecl
build-ecl:
	[ ! -d ecl-build ] || rm -rf ecl-build
	git clone -b $(ECL_BUILD_REF) $(ECL_BUILD_REPO) --depth 1 ecl-build
	yarn --cwd ./ecl-build install
	# Add ECL dependencies that cannot be required by ECL.
	# @see https://github.com/ec-europa/europa-component-library#warning-momentjs
	yarn --cwd ./ecl-build add moment@2.29.1 -W
	yarn --cwd ./ecl-build add svg4everybody@2.1.9 -W
	yarn --cwd ./ecl-build dist:presets

## compile-sass: compile SASS.
.PHONY: compile-sass
compile-sass:
	[ ! -d ./ecl-build/oe_theme_sass ] || rm -rf ./ecl-build/oe_theme_sass
	[ ! -d ./ecl-build/oe_theme_css ] || rm -rf ./ecl-build/oe_theme_css
	cp -r sass ./ecl-build/oe_theme_sass
	yarn --cwd ./ecl-build sass -I node_modules/ --style=expanded oe_theme_sass:oe_theme_css
	[ ! -d ./css ] || rm -rf ./css
	mv ./ecl-build/oe_theme_css css
# SASS duplicates the "oe_theme_css" inside the target directory.
# @todo fix this.
	[ ! -d ./css/oe_theme_css ] || rm -rf ./css/oe_theme_css

## copy-ecl-dist: build ECL.
.PHONY: copy-dist
copy-dist:
	[ ! -d dist ] || rm -rf dist
	mkdir dist
	mkdir dist/js
	cp -r ./ecl-build/dist/packages/ec dist/ec
	cp -r ./ecl-build/dist/packages/eu dist/eu
	cp ./ecl-build/node_modules/moment/min/moment.min.js ./dist/js
	cp ./ecl-build/node_modules/svg4everybody/dist/svg4everybody.js ./dist/js

## copy-twig	: copy ECL twigs in the destination directory.
.PHONY: copy-twig
copy-twig:
	bash ./scripts/copy-twig-templates.sh

## site: Build and install site.
.PHONY: site
site: build-site install-site

## build-site: build site.
.PHONY: build-site
build-site:
	rm -rf build
	rm -rf vendor
	rm -f composer.lock
	composer install

## install-site: install site.
.PHONY: install-site
install-site:
	./vendor/bin/run drupal:site-setup
	./vendor/bin/run drupal:site-install

## shell-node: login onto node container.
.PHONY: shell-node
shell-node:
	docker-compose exec node bash

# https://stackoverflow.com/a/6273809/1826109
%:
	@:
