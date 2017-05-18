const path = require('path');

const is_prod = process.env.NODE_ENV === 'production';
const source_folder = __dirname + '/node_modules/@ec-europa';

module.exports = {

  styles: [{
    entry: path.resolve(source_folder, 'ecl-components-preset-base/index.scss'),
    dest: path.resolve(__dirname, 'styles/base.css'),
    options: {
      sourceMap: is_prod ? 'file' : true
    }
  }],

  copy: [{
    from: path.resolve(source_folder, 'ecl-icons/fonts'),
    to: path.resolve(__dirname, 'fonts')
  }, {
    from: path.resolve(source_folder, 'ecl-logos/images'),
    to: path.resolve(__dirname, 'images')
  }]

};
