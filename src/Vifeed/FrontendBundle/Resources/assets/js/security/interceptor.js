'use strict';

// This http interceptor listens for authentication failures
angular.module('security.interceptor', ['security.retryQueue'])
.factory('securityInterceptor', ['$injector', 'securityRetryQueue', '$q', function ($injector, securityRetryQueue, $q) {
  return {
    response: function (response) {
      // do something on success
      return response || $q.when(response);
    },
    responseError: function (rejection) {
      if (/^\/api\//.test(rejection.config.url) && rejection.config.url !== Routing.generate('sign_in')) {
        if (rejection.status === 401 || rejection.status === 403) {
          // The request bounced because it was not authorized - add a new request to the retry queue
          return securityRetryQueue.pushRetryFn('unauthorized-server', function retryRequest() {
            // We must use $injector to get the $http service to prevent circular dependency
            return $injector.get('$http')(rejection.config);
          });
        }
      }

      return $q.reject(rejection);
    }
  };
}])
// We have to add the interceptor to the queue as a string because the interceptor depends upon service instances that are not available in the config block.
.config(['$httpProvider', function ($httpProvider) {
  $httpProvider.interceptors.push('securityInterceptor');
}]);
