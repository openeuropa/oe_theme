#!/bin/bash
# To build the theme using a development version of ECL set its development branch in .env.dist
# and make sure that ECL_BUILD is set to "dev".
. .env.dist
if [ "$ECL_BUILD" = "dev" ]; then
  echo "Building from development copy..."
  make ecl-dev
else
  echo "Building from stable release..."
  # The CI images use pnpm instead of npm since 2026-11-11. Install npm until
  # the repo migrates to using pnpm.
  if ! command -v npm >/dev/null; then
    pnpm add -g npm@11
  fi
  npm install --unsafe-perm
  # Apply the ECL patches explicitly.
  ./node_modules/.bin/patch-package
  NODE_ENV=production npm run build
fi
