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
    { from: path.resolve(nodeModules, 'ecl-twig/src/ec/packages'), patterns: '*/*.twig', to: path.resolve(outputFolder, 'templates/components') },
  ]
};
