const path = require('path');
const pkg = require('./package.json');

const isProd = process.env.NODE_ENV === 'production';
const outputFolder = __dirname;

const nodeModules = __dirname + '/node_modules';

// SCSS includePaths
const includePaths = [nodeModules];

const banner = `${pkg.name} - ${
  pkg.version
} Built on ${new Date().toISOString()}`;

module.exports = {
  styles: [
    {
      entry: path.resolve(__dirname, 'sass/style.scss'),
      dest: path.resolve(outputFolder, 'css/style.css'),
      options: {
        banner,
        includePaths,
        sourceMap: isProd ? 'file' : true,
      },
    },
  ],
  copy: [
    { from: path.resolve(nodeModules, '@ecl/ec-preset-website/dist'), to: path.resolve(outputFolder, 'dist') },
    { from: path.resolve(nodeModules, '@ecl/ec-preset-legacy-website/dist'), to: path.resolve(outputFolder, 'dist') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-accordion2/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-blockquote/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-breadcrumb/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-button/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-card/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-expandable/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-file/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-footer/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-gallery/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-hero-banner/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-icon/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-inpage-navigation/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-language-list/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-link/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-media-container/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-menu-legacy/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-message/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-page-banner/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-page-header/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-pagination/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-radio/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-search-form/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-site-header/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-skip-link/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-social-media-follow/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-social-media-share/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-table/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-tag/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-text-input/*.twig', to: path.resolve(outputFolder, 'components') },
    { from: path.resolve(nodeModules, '@ecl-twig'), patterns: 'ec-component-timeline/*.twig', to: path.resolve(outputFolder, 'components') },
  ]
};
