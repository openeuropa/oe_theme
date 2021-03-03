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
    {
      entry: path.resolve(__dirname, 'sass/print.scss'),
      dest: path.resolve(outputFolder, 'css/print.css'),
      options: style_options,
    },
  ],
  copy: [
    { from: path.resolve(nodeModules, '@ecl/ec-preset-editor/dist'), to: path.resolve(outputFolder, 'dist') },
    { from: path.resolve(nodeModules, '@ecl/ec-preset-website/dist'), to: path.resolve(outputFolder, 'dist/ec') },
    { from: path.resolve(nodeModules, '@ecl/eu-preset-website/dist'), to: path.resolve(outputFolder, 'dist/eu') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-accordion2/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-blockquote/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-breadcrumb-core/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-breadcrumb-standardised/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-button/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-card/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-description-list/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-dropdown-legacy/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-expandable/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-fact-figures/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-file/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-footer-core/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-footer-standardised/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-gallery/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-hero-banner/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-icon/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-inpage-navigation/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-language-list/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-link/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-media-container/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-menu/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-message/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-page-banner/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-page-header-core/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-page-header-standardised/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-pagination/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-radio/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-search-form/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-site-header-core/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-site-header-standardised/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-skip-link/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-social-media-follow/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-social-media-share/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-table/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-tag/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-text-input/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-timeline/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-datepicker/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, 'svg4everybody/dist'), patterns: 'svg4everybody.min.js', to: path.resolve(outputFolder, 'dist/js') },
  ]
};
