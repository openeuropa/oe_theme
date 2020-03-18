const path = require('path');
const pkg = require('./package.json');

const isProd = process.env.NODE_ENV === 'production';
const outputFolder = __dirname;

const nodeModules = __dirname + '/node_modules';

// SCSS includePaths
const includePaths = [nodeModules];

const style_options = {
  includePaths,
  sourceMap: isProd ? 'none' : true,
};

var copy = [
  { from: path.resolve(nodeModules, '@ecl/ec-preset-editor/dist'), to: path.resolve(outputFolder, 'dist') },
  { from: path.resolve(nodeModules, '@ecl/ec-preset-legacy-website/dist'), to: path.resolve(outputFolder, 'dist/ec') },
  { from: path.resolve(nodeModules, '@ecl/eu-preset-legacy-website/dist'), to: path.resolve(outputFolder, 'dist/eu') },
  { from: path.resolve(nodeModules, 'svg4everybody/dist'), patterns: 'svg4everybody.min.js', to: path.resolve(outputFolder, 'dist/js') },
];

const components = [
  'accordion2',
  'blockquote',
  'breadcrumb',
  'button',
  'card',
  'description-list',
  'dropdown-legacy',
  'expandable',
  'file',
  'footer',
  'gallery',
  'hero-banner',
  'icon',
  'inpage-navigation',
  'language-list',
  'link',
  'media-container',
  'menu-legacy',
  'message',
  'page-banner',
  'page-header',
  'pagination',
  'radio',
  'search-form',
  'site-header',
  'site-header-core',
  'site-header-harmonised',
  'site-header-standardised',
  'skip-link',
  'social-media-follow',
  'social-media-share',
  'table',
  'tag',
  'text-input',
  'timeline',
];

components.forEach(function (name) {
  copy.push({
    from: path.resolve(nodeModules, '@ecl-twig'),
    patterns: 'ec-component-' + name + '/*.twig',
    to: path.resolve(outputFolder, 'components/ec')
  });
  copy.push({
    from: path.resolve(nodeModules, '@ecl-twig'),
    patterns: 'ec-component-' + name + '/*.twig',
    to: path.resolve(outputFolder, 'components/eu')
  });
});

module.exports = {
  styles: [
    {
      entry: path.resolve(__dirname, 'sass/style-ec.scss'),
      dest: path.resolve(outputFolder, 'css/style-ec.css'),
      options: style_options,
    },
    {
      entry: path.resolve(__dirname, 'sass/style-eu.scss'),
      dest: path.resolve(outputFolder, 'css/style-eu.css'),
      options: style_options,
    },
  ],
  copy: copy
};
