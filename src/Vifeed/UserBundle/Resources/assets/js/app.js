// (function () {
//   'use strict';

//   // private variables for mock server
//   var apiAuthUrl = '/api/user/login_check', //Routing.generate('api_fos_user_security_check'), //'/api/auth',
//     _authenticated = false,
//     _username = null,
//     _id = null;

//   // angular.module('authtest', ['http-auth-interceptor', 'authentication'], function($interpolateProvider) {
//   //   $interpolateProvider.startSymbol('{[{');
//   //   $interpolateProvider.endSymbol('}]}');
// });

var userApp = angular.module('userApp', ['auth', 'http-auth-interceptor', 'authentication', 'ui.bootstrap.tabs', 'templates-angularUiBootstrapTabs'], function($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{');
    $interpolateProvider.endSymbol('}]}');
}).config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/sign-in', {
            templateUrl: '/bundles/vifeeduser/partials/sign-in.html', 
            controller: SigninController
        })
        .when('/sign-up', {
            templateUrl: '/bundles/vifeeduser/partials/sign-up.html', 
            controller: SignupController
        });
}]);


angular.module('app', [
  'ngRoute',
/*  'projectsinfo',
  'dashboard',
  'projects',
  'admin',
  'services.breadcrumbs',
  'services.i18nNotifications',*/
  'services.httpRequestTracker',
  'security'/*,
  'directives.crud',
  'templates.app',
  'templates.common'*/]);


//TODO: move those messages to a separate module
angular.module('app').constant('I18N.MESSAGES', {
  'login.reason.notAuthorized': "У вас нет необходимых прав доступа. Вы хотите войти под другим аккаунтом?",
  'login.reason.notAuthenticated': "Вы должны быть авторизованы для продолжения.",
  'login.error.invalidCredentials': "Неверный логин или пароль.",
  'login.error.serverError': "Возникла проблема во время аутентификаций: {{exception}}."
});

angular.module('services.localizedMessages', []).factory('localizedMessages', ['$interpolate', 'I18N.MESSAGES', function ($interpolate, i18nmessages) {

  var handleNotFound = function (msg, msgKey) {
    return msg || '?' + msgKey + '?';
  };

  return {
    get : function (msgKey, interpolateParams) {
      var msg =  i18nmessages[msgKey];
      if (msg) {
        return $interpolate(msg)(interpolateParams);
      } else {
        return handleNotFound(msg, msgKey);
      }
    }
  };
}]);


angular.module('app').controller('HeaderCtrl', ['$scope', '$location', '$route', 'security', 'httpRequestTracker',
  function ($scope, $location, $route, security, httpRequestTracker) {
  $scope.location = $location;
  //$scope.breadcrumbs = breadcrumbs;

  $scope.isAuthenticated = security.isAuthenticated;
  $scope.isAdmin = security.isAdmin;

  $scope.home = function () {
    if (security.isAuthenticated()) {
      $location.path('/dashboard');
    } else {
      $location.path('/projectsinfo');
    }
  };

  // $scope.isNavbarActive = function (navBarPath) {
  //   return navBarPath === breadcrumbs.getFirst().name;
  // };

  $scope.hasPendingRequests = function () {
    return httpRequestTracker.hasPendingRequests();
  };
}]);