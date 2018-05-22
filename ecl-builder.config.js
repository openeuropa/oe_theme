const path = require('path');
const is_prod = process.env.NODE_ENV === 'production';
const source_folder = __dirname + '/node_modules/@ecl';

module.exports = {

  scripts: [{
    entry: path.resolve(source_folder, 'ec-preset-full/ec-preset-full.js'),
    dest: path.resolve(__dirname, 'js/base.js'),
    options: {
      sourceMap: is_prod ? false : 'inline',
      moduleName: 'ECL'
    }
  }],

  styles: [
    {
      entry: 'sass/style.scss',
      dest: 'css/style.css',
      options: { sourceMap: is_prod ? 'file' : true }
    }
  ],

  copy: [
    { from: path.resolve(source_folder, 'generic-style-icon/fonts'), to: path.resolve(__dirname, 'fonts') },
    { from: path.resolve(source_folder, 'generic-component-form-checkbox/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'generic-component-form-feedback-message/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'generic-component-form-radio/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'generic-component-form-select/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'generic-component-logo/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'generic-component-message/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'generic-component-social-icon/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'ec-component-form-checkbox/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'ec-component-form-feedback-message/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'ec-component-form-radio/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'ec-component-form-select/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'ec-component-logo/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'ec-component-message/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder, 'ec-component-social-icon/images'), to: path.resolve(__dirname, 'images')},
    { from: path.resolve(source_folder), patterns: '*/*.twig', to: path.resolve(__dirname, 'templates/components') },
    { from: path.resolve(source_folder), patterns: '**/variants.json', to: path.resolve(__dirname, 'templates/components') }
  ]
};
