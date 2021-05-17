const path = require('path');
module.exports = function(grunt) {
  grunt.initConfig({
    copy: {
      main: {
        files: [
          {
            expand: true,
            cwd: 'node_modules/@ecl',
            src: ['**/*.twig'],
            dest: 'components/',
            rename: function (dest, src) {
              return dest + path.parse(src).dir + '/ecl-' + path.parse(src).base;
            }
          },
        ],
      },
    },
  });
  grunt.loadNpmTasks('grunt-contrib-copy');
};
