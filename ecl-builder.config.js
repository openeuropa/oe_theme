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
  scripts: [
    {
      entry: path.resolve(nodeModules, '@ecl/ec-preset-website/ec-preset-website.js'),
      dest: path.resolve(outputFolder, 'js/base.js'),
      options: {
        banner,
        moduleName: 'ECL',
        sourceMap: isProd ? false : 'inline',
      }
    }
  ],
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
    { from: path.resolve(nodeModules, '@ecl/generic-style-icon/fonts'), to: path.resolve(outputFolder, 'fonts') },
    { from: path.resolve(nodeModules, '@ecl/generic-component-form-checkbox/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/generic-component-form-feedback-message/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/generic-component-form-radio/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/generic-component-form-select/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/generic-component-logo/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/generic-component-message/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/generic-component-social-icon/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/ec-component-form-checkbox/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/ec-component-form-feedback-message/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/ec-component-form-radio/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/ec-component-form-select/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/ec-component-logo/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/ec-component-message/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl/ec-component-social-icon/images'), to: path.resolve(outputFolder, 'images')},
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-accordion/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-banner/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-blockquote/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-breadcrumb/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-button/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-context-nav/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-date-block/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-dialog/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-dropdown/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-featured-item/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-field/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-file/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-form-checkbox/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-form-label/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-form-radio/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-form-text-input/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-inpage-navigation/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-lang-select-page/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-lang-select-site/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-language-list/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-link/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-link-block/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-list-item/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-logo/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-message/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-meta/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-navigation-list/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-navigation-menu/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-page-header/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-pager/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-site-switcher/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-skip-link/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-social-media-link/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-component-social-icon/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'ec-style-image/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-accordion/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-banner/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-blockquote/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-breadcrumb/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-button/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-context-nav/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-date-block/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-dialog/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-featured-item/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-field/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-file/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-form-checkbox/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-form-label/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-form-radio/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-form-text-input/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-inpage-navigation/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-lang-select-page/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-lang-select-site/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-language-list/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-link-block/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-link/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-logo/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-message/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-meta/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-navigation-list/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-navigation-menu/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-page-header/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-pager/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-search-form/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-site-switcher/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-skip-link/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-social-media-link/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-component-social-icon/*.twig', to: path.resolve(outputFolder, 'templates/components') },
    { from: path.resolve(nodeModules, '@ecl'), patterns: 'generic-style-image/*.twig', to: path.resolve(outputFolder, 'templates/components') }
  ]
};
