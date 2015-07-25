angular.module('userApp').controller 'SignupCtrl', [
  '$scope', '$window', '$routeParams', 'security', 'ErrorProcessor', 'ProgressBar',
  ($scope, $window, $routeParams, security, ErrorProcessor, ProgressBar) ->
    $scope.errorList = []
    $scope.user =
      email: ''
      password: null
      passwordRepeat: null
      type: 'advertiser'
    $scope.isPublisher = false

    if $routeParams.userType? and $routeParams.userType is 'publisher'
      $scope.user.type = $routeParams.userType
      $scope.isPublisher = true

    # Click on the tab for advertiser
    $scope.setUserAsAdvertiser = ->
      $scope.user.type = 'advertiser'
      $scope.isPublisher = false

    # Click on the tab for publisher
    $scope.setUserAsPublisher = ->
      $scope.user.type = 'publisher'
      $scope.isPublisher = true

    # Sign in button handler
    $scope.signup = ->
      ProgressBar.start()
      userData =
        registration:
          email: $scope.user.email
          plainPassword:
            first: $scope.user.password
            second: $scope.user.passwordRepeat
          type: $scope.user.type
      # Reset the error messages
      $scope.errorList = []
      security.signup(userData).then -> $window.location = '/'
      .catch (response) ->
        $scope.errorList = ErrorProcessor.toList response.data.errors
      .finally( -> ProgressBar.stop())

    # Click on the "Enter" link below the submit button
    $scope.showLoginForm = -> security.showLogin()
]
