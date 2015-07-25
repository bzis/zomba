// Karma configuration
// Generated on Tue Apr 29 2014 18:46:20 GMT+0400 (MSK)

module.exports = function (config) {
  config.set({
    // base path that will be used to resolve all patterns (eg. files, exclude)
    basePath: '../',

    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: ['jasmine', 'chai'],

    // list of files / patterns to load in the browser
    files: [
      'https://maps.googleapis.com/maps/api/js?v=3&amp;sensor=false&amp;libraries=visualization',
      'http://vkontakte.ru/js/api/openapi.js',
      '../../../../../bower-vendor/jquery/dist/jquery.min.js',
      '../../../../../bower-vendor/parallax/deploy/jquery.parallax.min.js',
      '../../../../../bower-vendor/jquery.easy-pie-chart/dist/jquery.easypiechart.min.js',
      '../../../../../bower-vendor/lodash/dist/lodash.underscore.min.js',
      '../../../../../bower-vendor/angular/angular.js',
      '../../../../../bower-vendor/angular-route/angular-route.js',
      '../../../../../bower-vendor/angular-mocks/angular-mocks.js',
      '../../../../../bower-vendor/angular-resource/angular-resource.min.js',
      '../../../../../bower-vendor/angular-sanitize/angular-sanitize.min.js',
      '../../../../../bower-vendor/bootstrap-sass-twbs/vendor/assets/javascripts/bootstrap/carousel.js',
      '../../../../../bower-vendor/bootstrap-sass-twbs/vendor/assets/javascripts/bootstrap/dropdown.js',
      '../../../../../vendor/friendsofsymfony/jsrouting-bundle/FOS/JsRoutingBundle/Resources/public/js/router.js',
      '../../../../../web/js/fos_js_routes.js',
      '../../../../../vendor/angular-ui/bootstrap/src/tabs/tabs.js',
      '../../../../../vendor/angular-ui/bootstrap/src/modal/modal.js',
      '../../../../../vendor/angular-ui/bootstrap/src/transition/transition.js',
      '../../../../../vendor/angular-ui/bootstrap/src/popover/popover.js',
      '../../../../../vendor/angular-ui/bootstrap/src/tooltip/tooltip.js',
      '../../../../../vendor/angular-ui/bootstrap/src/position/position.js',
      '../../../../../vendor/angular-ui/bootstrap/src/bindHtml/bindHtml.js',
      '../../../../../vendor/angular-ui/bootstrap/src/alert/alert.js',
      '../../../../../vendor/angular-ui/bootstrap/src/dropdown/dropdown.js',
      '../../../../../vendor/angular-ui/bootstrap/src/pagination/pagination.js',
      '../../../../../vendor/angular-ui/bootstrap/src/buttons/buttons.js',
      '../../../../../vendor/angular-ui/ui-utils/modules/mask/mask.js',
      '../../../../../vendor/angular-ui/ui-utils/modules/event/event.js',
      '../../../../../bower-vendor/angulartics/dist/angulartics.min.js',
      '../../../../../bower-vendor/angulartics/dist/angulartics-ga.min.js',
      '../../../../../tmp/*.js',
      '../../../../../bower-vendor/ladda-bootstrap/dist/spin.min.js',
      '../../../../../bower-vendor/ladda-bootstrap/dist/ladda.min.js',
      '../../../../../bower-vendor/angular-ui-select2/src/select2.js',
      '../../../../../bower-vendor/select2/select2.min.js',
      '../../../../../bower-vendor/select2/select2_locale_ru.js',
      '../../../../../bower-vendor/ngprogress-lite/ngprogress-lite.min.js',
      '../../../../../bower-vendor/xapu-angular-cookies/cookiesModule.js',
      '../../../../../bower-vendor/moment/min/moment-with-locales.min.js',
      '../../../../../bower-vendor/twix/bin/twix.min.js',
      '../../../../../bower-vendor/angular-moment/angular-moment.min.js',
      '../../../../../bower-vendor/bootstrap-daterangepicker/daterangepicker.js',
      '../../../../../bower-vendor/highcharts-release/highcharts.src.js',
      '../../../../../bower-vendor/highcharts-ng/dist/highcharts-ng.min.js',
      '../../../../../bower-vendor/crypto-js/components/core-min.js',
      '../../../../../bower-vendor/crypto-js/components/sha1-min.js',
      '../../../../../bower-vendor/crypto-js/components/md5-min.js',
      '../../../../../bower-vendor/crypto-js/components/enc-base64-min.js',
      '../../../../../bower-vendor/angular-google-maps/dist/angular-google-maps.min.js',
      '../../../../../bower-vendor/angular-socket-io/socket.min.js',
      '../../../../../bower-vendor/angular-socket-io/mock/socket-io.js',
      'Resources/assets/js/mixin/*.coffee',
      'Resources/assets/js/datepicker/datepicker.coffee',
      'Resources/assets/js/datepicker/**/*.coffee',
      'Resources/assets/js/datepicker/**/*.js',
      'Resources/assets/js/i18n/**/*.coffee',
      'Resources/assets/js/security/**/*.coffee',
      'Resources/assets/js/security/**/*.js',
      'Resources/assets/js/profile/*.coffee',
      'Resources/assets/js/profile/**/*.coffee',
      'Resources/assets/js/kernel/*.coffee',
      'Resources/assets/js/kernel/**/*.js',
      'Resources/assets/js/kernel/**/*.coffee',
      'Resources/assets/js/publisher/*.coffee',
      'Resources/assets/js/publisher/**/*.js',
      'Resources/assets/js/publisher/**/*.coffee',
      'Resources/assets/js/analytics/*.coffee',
      'Resources/assets/js/analytics/**/*.coffee',
      'Resources/assets/js/advertiser/*.coffee',
      'Resources/assets/js/advertiser/**/*.coffee',
      'Resources/assets/js/*-app.coffee',
      'Tests/unit/mock/*.coffee',
      'Tests/unit/main/*.spec.js',
      'Tests/unit/mixin/**/*.spec.coffee',
      'Tests/unit/i18n/**/*.spec.coffee',
      'Tests/unit/kernel/**/*.spec.coffee',
      'Tests/unit/analytics/**/*.spec.coffee',
      'Tests/unit/advertiser/**/*.spec.coffee',
      'Tests/unit/publisher/**/*.spec.coffee'
    ],

    // list of files to exclude
    exclude: [],

    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {
      '**/*.coffee': ['coffee']
    },

    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['progress'],

    // web server port
    port: 9876,

    // enable / disable colors in the output (reporters and logs)
    colors: true,

    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,

    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: true,

    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: ['Chrome', 'Firefox'],

    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: true
  });
};
