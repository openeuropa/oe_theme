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
            dest: 'ecl_components/',
            rename: function (dest, src) {
              return dest + path.parse(src).dir + '/ecl-' + path.parse(src).base;
            }
          },
        ],
      },
    },
  });
  grunt.loadNpmTasks('grunt-contrib-copy');

  grunt.registerTask('clean-ecl-templates', 'Remove ECL twig templates from the components directory', function() {
    const fs = require('fs');

    grunt.file.expand('components/**/ecl-*.html.twig').forEach(function(file) {
      grunt.file.delete(file);
      grunt.log.writeln('Deleted ' + file);
    });

    // Remove empty directories deepest-first so parent dirs get cleaned up too.
    grunt.file.expand({filter: 'isDirectory'}, 'components/**').sort(function(a, b) {
      return b.split('/').length - a.split('/').length;
    }).forEach(function(dir) {
      if (fs.readdirSync(dir).length === 0) {
        fs.rmdirSync(dir);
        grunt.log.writeln('Removed empty directory ' + dir);
      }
    });
  });

  grunt.registerTask('build', ['clean-ecl-templates', 'copy']);
};
