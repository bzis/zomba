angular.module('publisher').controller 'FinanceCtrl', [
  '$scope', '$controller', '$window', '$routeParams', '$location', '$timeout', 'security', 'earnings', 'withdrawals',
  ($scope, $controller, $window, $routeParams, $location, $timeout, security, earnings, withdrawals) ->
    'use strict'

    return unless security.isAuthenticated()

    $controller 'DatepickerCtrl', {
      $scope: $scope
      $window: $window
      $routeParams: $routeParams
      $location: $location
    }

    $timeout ( -> $scope.balance = security.currentUser.balance), 500
    $scope.platforms = earnings.platforms
    $scope.totalEarned = earnings.total
    $scope.withdrawals = withdrawals.withdrawals
    $scope.totalWithdrawals = withdrawals.total

    $scope.changeFinancePeriod = -> $scope.changePeriod '/finance'
    $scope.goToWallets = -> $location.path '/finance/wallet'
]
