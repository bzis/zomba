# The loginToolbar directive is a reusable widget that can show login or logout buttons
# and information the current authenticated user
angular.module('security.login.toolbar', []).directive 'loginToolbar', [
  '$window', '$location', 'security', ($window, $location, security) ->
    'use strict'

    {
      templateUrl: '/bundles/vifeedfrontend/partials/security/toolbar.tpl.html'
      restrict: 'EA'
      replace: true
      scope: true
      link: ($scope, $element, $attrs) ->
        $scope.isAuthenticated = security.isAuthenticated
        $scope.signup = security.goToSignUpPage

        $scope.login = ->
          security.showLogin()
          angular.element('.navbar-toggle:visible').trigger 'click'
          return

        $scope.logout = (redirectPath) ->
          security.logout().then ->
            $window.location = redirectPath if redirectPath?

        $scope.$watch(
          ( -> security.currentUser),
          (currentUser) -> $scope.currentUser = currentUser
        )
    }
]
