angular.module('userApp').controller 'ConfirmCtrl', [
  '$scope', '$routeParams', 'security', 'ErrorProcessor', 'ProgressBar',
  ($scope, $routeParams, security, ErrorProcessor, ProgressBar) ->
    $scope.type = 'info'
    $scope.progressMessage = 'Подтверждение вашей регистрации...'
    $scope.errorList = []

    $scope.confirmRegistration = ->
      ProgressBar.start()
      security.confirmUser($routeParams.token).then (response) ->
        $scope.type = 'success'
        $scope.progressMessage = 'Ваша регистрация успешно подтверждена'
      .catch (response) ->
        $scope.errorList = ErrorProcessor.toList response.data.errors
        unless $scope.errorList.length
          $scope.errorList.push 'Передан неверный токен'
      .finally( -> ProgressBar.stop())
]
