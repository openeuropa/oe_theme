#!/bin/sh -e

# This script will create a release package suitable for use on production
# instances. This package excludes all tests and development tools.

# Define paths.
PROJECT_ROOT=$(pwd)
RELEASE_PATH=${PROJECT_ROOT}/oe_theme

# Get the release number.
VERSION=$(git describe --tags)
if [ $? -ne 0 ]; then
  echo "No release tag associated with the current HEAD."
  exit 1
fi

cd ${PROJECT_ROOT}

# Create release folder.
mkdir ${RELEASE_PATH}

# Undo vandalism to composer.json.
git checkout composer.json
rm composer.lock

# Install production dependencies.
# Todo: Not needed if we exclude the vendor folder.
sudo chmod -R a+w build/
rm -rf build/
rm -rf vendor/
docker-compose exec -u web web composer install --no-dev

# Copy production files.
# Todo: exclude the vendor folder?
cp -r \
  config/ \
  css/ \
  js/ \
  modules/ \
  templates/ \
  vendor/ \
  composer.json \
  LICENCE.txt \
  oe_theme.* \
  README.md \
  ${RELEASE_PATH}

# Add release metadata.
DATE=$(date +"%Y-%m-%d")
TIMESTAMP=$(date +%s)
echo "
; Information added by OpenEuropa packaging script on ${DATE}
version = ${VERSION}
timestamp = ${TIMESTAMP}" >> ${RELEASE_PATH}/oe_theme.info.yml

# Create release archive.
tar -czf oe_theme.tar.gz -C ${PROJECT_ROOT} oe_theme/
