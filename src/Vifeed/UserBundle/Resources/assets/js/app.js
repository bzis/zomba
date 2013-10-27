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

var userApp = angular.module('userApp', ['http-auth-interceptor', 'authentication', 'ui.bootstrap.tabs', 'ui-templates'], function($interpolateProvider) {
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