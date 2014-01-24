'use strict';
module.exports = function(grunt) {
  grunt.initConfig({
    aws: grunt.file.readJSON('/home/deploy/grunt-aws.json'),
    invalidate_cloudfront: {
      options: {
        key: '<%= aws.key %>',
        secret: '<%= aws.secret %>',
        distribution: 'EWM7POCBEJEWK'
      },
      production: {
        files: [{
          expand: true,
          cwd: './web/',
          src: ['js/**/*', 'css/**/*', 'bundles/**/*'],
          filter: 'isFile',
          dest: ''
        }]
      }
    },
    s3: {
      options: {
        key: '<%= aws.key %>',
        secret: '<%= aws.secret %>',
        bucket: '<%= aws.bucket %>',
        access: 'public-read',
        headers: {
          // Two Year cache policy (1000 * 60 * 60 * 24 * 730)
          'Cache-Control': 'max-age=630720000, public',
          'Expires': new Date(Date.now() + 63072000000).toUTCString()
        },
      },
      js_and_css: {
        options: {
          key: '<%= aws.key %>',
          secret: '<%= aws.secret %>',
          bucket: '<%= aws.bucket %>',
          access: 'public-read'
        },
        upload: [{
          // The gzip js files
          src: "web/js/**/*.js",
          dest: "js",
          rel: "web/js",
          options: { gzip: true }
        }, {
          // The gzip css files
          src: "web/css/**/*.css",
          dest: "css",
          rel: "web/css",
          options: { gzip: true }
        }]
      },
      assets: {
        upload: [{
          src: "web/bundles/**",
          dest: "bundles",
          rel: "web/bundles"
        }]
      },
      fonts: {
        upload: [{
          src: "bower-vendor/sass-bootstrap/fonts/**",
          dest: "fonts",
          rel: "bower-vendor/sass-bootstrap/fonts"
        }]
      }
    },
    curl: {
      'tmp/google-fonts/OpenSans.scss': 'http://fonts.googleapis.com/css?family=Open+Sans:400,300,700&subset=latin,cyrillic-ext',
      'tmp/google-fonts/OleoScriptSwashCaps.scss': 'http://fonts.googleapis.com/css?family=Oleo+Script+Swash+Caps'
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
  grunt.loadNpmTasks('grunt-curl');
  grunt.loadNpmTasks('grunt-invalidate-cloudfront');
  grunt.registerTask('default', ['html2js', 'curl']);
  grunt.registerTask('after_assetic_dump', ['s3', 'invalidate_cloudfront']);
};
