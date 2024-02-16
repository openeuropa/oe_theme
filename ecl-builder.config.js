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
    { from: path.resolve(nodeModules, '@ecl/preset-reset/dist'), to: path.resolve(outputFolder, 'dist/preset-reset') },
    { from: path.resolve(nodeModules, '@ecl/preset-ec/dist'), to: path.resolve(outputFolder, 'dist/ec') },
    { from: path.resolve(nodeModules, '@ecl/preset-eu/dist'), to: path.resolve(outputFolder, 'dist/eu') },
    { from: path.resolve(nodeModules, 'svg4everybody/dist'), patterns: 'svg4everybody.js', to: path.resolve(outputFolder, 'dist/js') },
    { from: path.resolve(nodeModules, 'moment/min'), patterns: 'moment.min.js', to: path.resolve(outputFolder, 'dist/js') },
  ]
};
