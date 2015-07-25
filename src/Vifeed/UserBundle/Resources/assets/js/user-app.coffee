angular.module('userApp', ['kernel']).config [
  '$routeProvider', ($routeProvider) ->
    'use strict'

    $routeProvider
      .when '/forgot', {
        templateUrl: '/bundles/vifeeduser/partials/user/password-forgotten.html'
        controller: 'PasswordForgotCtrl'
      }
      .when '/update', {
        templateUrl: '/bundles/vifeeduser/partials/user/password-update.html'
        controller: 'PasswordUpdateCtrl'
      }
      .when '/:userType?', {
        templateUrl: '/bundles/vifeeduser/partials/user/sign-up.html'
        controller: 'SignupCtrl'
      }
      .when '/confirm/:token', {
        templateUrl: '/bundles/vifeeduser/partials/user/sign-up-confirmation.html'
        controller: 'ConfirmCtrl'
      }
      .otherwise redirectTo: '/'
]
