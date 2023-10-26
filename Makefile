include .env

DOCKER_COMPOSE ?= docker-compose
DOCKER_CMD ?= exec

## default        : Run `make` without parameters to create the build from scratch.
default: build-ecl

# Files to make
.env:
	cp .env.dist .env

## build-ecl	: build ECL.
.PHONY: build-ecl
build-ecl:
	[ ! -d ecl-build ] || rm -rf ecl-build
	git clone https://github.com/ec-europa/europa-component-library.git -b $(ECL_BUILD_REF) --depth 1 ecl-build
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) node yarn --cwd ./ecl-build install
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) node yarn --cwd ./ecl-build dist:presets

## copy-ecl-dist	: build ECL.
.PHONY: copy-dist
copy-dist: build-ecl
	[ ! -d dist/ec ] || rm -rf dist/ec
	[ ! -d dist/eu ] || rm -rf dist/eu
	cp -r ./ecl-build/dist/packages/ec dist/ec
	cp -r ./ecl-build/dist/packages/eu dist/eu

## copy-twig	: copy ECL twigs in the destination directory.
.PHONY: copy-twig
copy-twig:
	@$(DOCKER_COMPOSE) $(DOCKER_CMD) node bash ./copy-twig-templates.sh

# https://stackoverflow.com/a/6273809/1826109
%:
	@:
