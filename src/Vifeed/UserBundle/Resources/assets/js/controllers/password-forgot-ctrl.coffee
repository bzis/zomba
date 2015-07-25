angular.module('userApp').controller 'PasswordForgotCtrl', [
  '$scope', 'security', 'ProgressBar', ($scope, security, ProgressBar) ->
    $scope.errorList = []
    $scope.email = null
    $scope.linkSent = false

    $scope.sendPasswordLink = ->
      $scope.errorList = []
      ProgressBar.start()
      security.sendPasswordLink($scope.email).then (response) ->
        $scope.linkSent = true
      .catch (response) -> $scope.errorList.push(response.data.message)
      .finally( -> ProgressBar.stop())
]
