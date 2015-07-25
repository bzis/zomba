# The LoginFormController provides the behaviour behind a reusable form to allow users to authenticate.
# This controller and its template (login/form.tpl.html) are used in a modal dialog box by the security service.
angular.module('security.login.form', ['i18n']).controller 'LoginFormController', [
  '$rootScope', '$scope', '$window', 'security', 'LocalizedMessages', 'ProgressBar',
  ($rootScope, $scope, $window, security, LocalizedMessages, ProgressBar) ->
    'use strict'

    # The model for this form
    $scope.user =
      email: null
      password: null
      rememberMe: false

    # Any error message from failing to login
    $scope.authError = null

    # The reason that we are being asked to login - for instance because we tried to access something to which we are not authorized
    # We could do something diffent for each reason here but to keep it simple...
    $scope.authReason = null

    if security.getLoginReason()
      $scope.authReason = if security.isAuthenticated()
        LocalizedMessages.get('login.reason.notAuthorized')
      else
        LocalizedMessages.get('login.reason.notAuthenticated')

    # Attempt to authenticate the user specified in the form's model
    $scope.login = ->
      # Start display of a loading progress
      ProgressBar.start()
      # Clear any previous security errors
      $scope.authError = null

      # Try to login
      security.login($scope.user.email, $scope.user.password, $scope.user.rememberMe).then (response) ->
        $window.location.reload()
      .catch (response) ->
        if response.status is 401
          $scope.authError = LocalizedMessages.get 'login.error.invalidCredentials'
        else
          # If we get here then there was a problem with the login request to the server
          $scope.authError = LocalizedMessages.get('login.error.serverError', exception: response.data.message)
      .finally( -> ProgressBar.stop())
]
