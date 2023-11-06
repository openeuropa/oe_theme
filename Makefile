include .env

DOCKER_COMPOSE ?= docker-compose
DOCKER_CMD ?= exec

## default        : Run `make` without parameters to create the build from scratch.
default: build-ecl copy-dist copy-twig

# Files to make
.env:
	cp .env.dist .env

## build-site	: build site.
.PHONY: build-site
build-site:
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) web rm -rf build
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) web rm -rf vendor
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) web rm -f composer.lock
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) web composer install

## install-site	: install site.
.PHONY: install-site
install-site:
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) web ./vendor/bin/run drupal:site-setup
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) web ./vendor/bin/run drupal:site-install

## site	: Build and install site.
.PHONY: site
site: build-site install-site

## build-ecl	: build ECL.
.PHONY: build-ecl
build-ecl:
	[ ! -d ecl-build ] || rm -rf ecl-build
	git clone -b $(ECL_BUILD_REF) https://github.com/ec-europa/europa-component-library.git --depth 1 ecl-build
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) node yarn --cwd ./ecl-build install
	# Add ECL dependencies that cannot be required by ECL.
	# @see https://github.com/ec-europa/europa-component-library#warning-momentjs
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) node yarn --cwd ./ecl-build add moment@2.29.1 -W
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) node yarn --cwd ./ecl-build add svg4everybody@2.1.9 -W
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) node yarn --cwd ./ecl-build dist:presets

## copy-ecl-dist	: build ECL.
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
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) node bash ./scripts/copy-twig-templates.sh

# https://stackoverflow.com/a/6273809/1826109
%:
	@:
