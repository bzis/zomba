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
      assets: {
        files: [{
          expand: true,
          cwd: './web/',
          src: ['js/**/*', 'css/**/*', 'bundles/**/*', 'images/**/*', 'videos/**/*'],
          filter: 'isFile',
          dest: ''
        }]
      },
      videos: {
        files: [{
          expand: true,
          cwd: './tmp/',
          src: ['videos/**/*'],
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
      },
      select2: {
        upload: [
        {
          src: "bower-vendor/select2/*.png",
          dest: "images/select2",
          rel: "bower-vendor/select2"
        },
        {
          src: "bower-vendor/select2/*.gif",
          dest: "images/select2",
          rel: "bower-vendor/select2"
        }
        ],
      },
      videos: {
        upload: [{
          src: "tmp/videos/**",
          dest: "videos",
          rel: "tmp/videos"
        }]
      },
    },
    imagemin: {                          // Task
      dynamic: {                         // Another target
        files: [{
          expand: true,                  // Enable dynamic expansion
          cwd: 'web/bundles/',                   // Src matches are relative to this path
          src: ['**/*.{png,jpg,gif}']   // Actual patterns to match
        }]
      }
    },
    curl: {
      'tmp/google-fonts/OpenSans.scss': 'http://fonts.googleapis.com/css?family=Open+Sans:400,300,700&subset=latin,cyrillic-ext',
      'tmp/google-fonts/OleoScriptSwashCaps.scss': 'http://fonts.googleapis.com/css?family=Oleo+Script+Swash+Caps',
      'tmp/google-fonts/Lora.scss': 'http://fonts.googleapis.com/css?family=Lora:400,700&subset=cyrillic,latin',
    },
    replace: {
      select2: {
        src: ['web/css/**/*.css'],
        overwrite: true,                 // overwrite matched source files
        replacements: [{
          from: /(?:\.\.\/)(select2\.png|select2x2\.png|select2-spinner\.gif)/g,
          to: 'http://stage-cdn.vifeed.co/images/select2/$1'
        }]
      },
      bundles: {
        src: ['web/css/**/*.css'],
        overwrite: true,                 // overwrite matched source files
        replacements: [{
          from: /url\((["'])\/bundles\//,
          to: 'url($1http://stage-cdn.vifeed.co/bundles/'
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

    ngconstant: {
      options: {
        space: '  ',
        wrap: '"use strict";\n\n {%= __ngModule %}',
        name: 'zmbk.config',
        dest: 'tmp/frontend_config.js',
        constants: {
          'APP.CONFIG': '',
        }
      },
      development: {
        constants: {
          'APP.CONFIG': grunt.file.readJSON('app/config/frontend_dev.json'),
        }
      },
      production: {
        constants: {
          'APP.CONFIG': grunt.file.readJSON('app/config/frontend_prod.json'),
        }
      },
      staging: {
        constants: {
          'APP.CONFIG': grunt.file.readJSON('app/config/frontend_stage.json'),
        }
      }
    },

    responsive_videos: {
      myTask: {
        options: {
          sizes: [{
            width: 846,
            poster: true
          }]
          // ,
          // encodes:[{
          //   webm: [
          //     {'-vcodec': 'libvpx'},
          //     {'-acodec': 'libvorbis'},
          //     {'-crf': '12'},
          //     {'-b:v': '1.5M',},
          //     {'-q:a': '100'}
          //   ]
          // }]
        },
        files: [{
          expand: true,
          src: ['*.{mov,mp4}'],
          cwd: '../videos',
          dest: 'tmp/videos'
        }]
      }
    },
  });

  grunt.loadNpmTasks('grunt-ng-constant');
  grunt.loadNpmTasks('grunt-text-replace');
  grunt.loadNpmTasks('grunt-html2js');
  grunt.loadNpmTasks('grunt-s3');
  grunt.loadNpmTasks('grunt-contrib-imagemin');
  grunt.loadNpmTasks('grunt-curl');
  grunt.loadNpmTasks('grunt-responsive-videos');
  grunt.loadNpmTasks('grunt-invalidate-cloudfront');
  grunt.registerTask('default', ['html2js', 'ngconstant:production', 'curl', 'imagemin']);
  grunt.registerTask('release_videos', ['responsive_videos', 's3:videos', 'invalidate_cloudfront:videos']);
  grunt.registerTask('after_assetic_dump', ['replace', 's3:js_and_css', 's3:assets', 's3:fonts', 's3:select2', 'invalidate_cloudfront:assets']);
};
