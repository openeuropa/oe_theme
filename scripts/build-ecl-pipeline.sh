#!/bin/bash
# To build the theme using a development version of ECL set its development branch in .env.dist
# and make sure that ECL_BUILD is set to "dev".
. .env.dist
if [ "$ECL_BUILD" = "dev" ]; then
  echo "Building from development copy..."
  make ecl-dev
else
  echo "Building from stable release..."
  npm install --unsafe-perm
  NODE_ENV=production npm run build
fi
