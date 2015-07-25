angular.module('advertiser').controller 'ReplenishmentCompletedCtrl', [
  '$scope', '$routeParams', 'security', 'Orders',
  ($scope, $routeParams, security, Orders) ->
    'use strict'

    return unless security.isAuthenticated()

    $scope.state = 'loading'

    isValid = (params) ->
      return false if not params.InvId and not params.OutSum
      return false if not angular.isNumber(param.InvId) and params.InvId < 1
      return false if not angular.isNumber(param.OutSum) and params.OutSum < 1
      true

    isCompleted = (params) ->
      unless isValid(params)
        $scope.state = 'error'
        return

      $scope.orderId = params.InvId
      $scope.amount = params.OutSum

      Orders.complete($routeParams.InvId).then(
        (response) -> $scope.state = 'success',
        (response) -> $scope.state = 'waiting'
      )

    isCompleted($routeParams)
]
