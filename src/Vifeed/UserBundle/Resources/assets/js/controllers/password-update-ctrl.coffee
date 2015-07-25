angular.module('userApp').controller 'PasswordUpdateCtrl', [
  '$scope', '$routeParams', 'security', 'ProgressBar',
  ($scope, $routeParams, security, ProgressBar) ->
    if not $routeParams.token? or $routeParams.token.length is 0
      $scope.isError = true
      $scope.passwordUpdated = true
      return

    $scope.errorList = []
    $scope.passwordUpdated = false
    $scope.password =
      newOne: null
      newOneRepeated: null

    $scope.updatePasswords = ->
      ProgressBar.start()
      $scope.errorList = []
      security.updatePasswordByToken(
        $routeParams.token,
        first: $scope.password.newOne, second: $scope.password.newOneRepeated
      ).then (response) -> $scope.passwordUpdated = true
      .catch (response) -> $scope.errorList.push(response.data.message)
      .finally( -> ProgressBar.stop())

    $scope.showLoginForm = -> security.showLogin()
]
