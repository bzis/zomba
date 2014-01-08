'use strict';
module.exports = function(grunt) {
  grunt.initConfig({
    aws: grunt.file.readJSON('~/grunt-aws.json'),
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
        dev: {
          // These options override the defaults
          options: {
            encodePaths: true,
            maxOperations: 20
          },
          // Files to be uploaded.
          upload: [
            {
              src: 'prod.rb',
              dest: 'documents/prod.rb',
              options: { gzip: true }
            },
            {
              src: 'passwords.txt',
              dest: 'documents/ignore.txt',

              // These values will override the above settings.
              options: {
                bucket: 'some-specific-bucket',
                access: 'authenticated-read'
              }
            },
            {
              // Wildcards are valid *for uploads only* until I figure out a good implementation
              // for downloads.
              src: 'documents/*.txt',

              // But if you use wildcards, make sure your destination is a directory.
              dest: 'documents/'
            }
          ],

          // Files to be downloaded.
          download: [
            {
              src: 'documents/important.txt',
              dest: 'important_document_download.txt'
            },
            {
              src: 'garbage/IGNORE.txt',
              dest: 'passwords_download.txt'
            }
          ],

          del: [
            {
              src: 'documents/launch_codes.txt'
            },
            {
              src: 'documents/backup_plan.txt'
            }
          ],

          sync: [
            {
              // only upload this document if it does not exist already
              src: 'important_document.txt',
              dest: 'documents/important.txt',
              options: { gzip: true }
            },
            {
              // make sure this document is newer than the one on S3 and replace it
              options: { verify: true },
              src: 'passwords.txt',
              dest: 'documents/ignore.txt'
            }
          ]
        }
      },
      prod: {
        upload: [{
          // The regular js files
          src: "tmp/assets/js/**/*.js",
          dest: "js",
          rel:  "tmp/assets/js"
        }, {
          // The gzip js files
          src: "tmp/assets/js/**/*.js",
          dest: "jsgz",
          rel: "tmp/assets/js",
          options: { gzip: true }
        }, {
          // The regular css files
          src: "tmp/assets/css/**/*.css",
          dest: "css",
          rel: "tmp/assets/css"
        }, {
          // The gzip css files
          src: "tmp/assets/css/**/*.css",
          dest: "cssgz",
          rel: "tmp/assets/css",
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
