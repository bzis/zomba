angular.module('progressBar', []).factory('ProgressBar',
  ['$rootScope', '$interval', 'ngProgressLite', function ($rootScope, $interval, ngProgressLite) {
    'use strict';

    var bar = {},
        stopLoadingProgress = null;

    $rootScope.loadingProgress = false;

    // Starts a progress bar
    bar.start = function () {
      if (bar.isActive()) {
        return;
      }

      ngProgressLite.start();

      stopLoadingProgress = $interval(function () {
        $rootScope.loadingProgress = parseFloat(ngProgressLite.get().toFixed(2));
      }, 100);
    };

    // Checks whether a bar is active in the moment
    bar.isActive = function () {
      return stopLoadingProgress !== null;
    };

    // Stops execution of a progress bar
    bar.stop = function () {
      $interval.cancel(stopLoadingProgress);
      stopLoadingProgress = null;
      $rootScope.loadingProgress = false;
      ngProgressLite.done();
    };

    return bar;
  }]
);
