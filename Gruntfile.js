'use strict';
module.exports = function(grunt) {
  grunt.initConfig({
    aws: grunt.file.readJSON('/home/deploy/grunt-aws.json'),
    s3: {
      options: {
        key: '<%= aws.key %>',
        secret: '<%= aws.secret %>',
        bucket: '<%= aws.bucket %>',
        access: 'public-read',
        headers: {
          // Two Year cache policy (1000 * 60 * 60 * 24 * 730)
          "Cache-Control": "max-age=630720000, public",
          "Expires": new Date(Date.now() + 63072000000).toUTCString()
        },
      prod: {
        upload: [{
          // The regular js files
          src: "web/js/**/*.js",
          dest: "js",
          rel:  "web/js"
        }, {
          // The gzip js files
          src: "web/js/**/*.js",
          dest: "jsgz",
          rel: "web/js",
          options: { gzip: true }
        }, {
          // The regular css files
          src: "web/css/**/*.css",
          dest: "css",
          rel: "web/css"
        }, {
          // The gzip css files
          src: "web/css/**/*.css",
          dest: "cssgz",
          rel: "web/css",
          options: { gzip: true }
        }, {
          // The gzip css files
          src: "web/bundles/**",
          dest: "bundles",
          rel: "web/bundles"
        }]
      }
    },

    html2js: {
      module: 'templates',
      options: {
        rename: function(modulePath) {
          return modulePath.replace('../vendor/angular-ui/bootstrap/', '');
        }
      },
      angularUiBootstrapTabs: {
        src: ['vendor/angular-ui/bootstrap/template/tabs/*.html'],
        dest: 'tmp/angular-ui-tabs-templates.js'
      },
      angularUiBootstrapModal: {
        src: ['vendor/angular-ui/bootstrap/template/modal/*.html'],
        dest: 'tmp/angular-ui-modal-templates.js'
      }
    },
  });




  grunt.loadNpmTasks('grunt-html2js');
  grunt.loadNpmTasks('grunt-s3');
  //grunt.loadTasks('tasks');
  grunt.registerTask('default', ['html2js']);
  grunt.registerTask('after_assetic_dump', ['s3:dev']);
};
