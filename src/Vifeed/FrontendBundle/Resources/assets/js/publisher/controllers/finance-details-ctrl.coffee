angular.module('publisher').controller 'FinanceDetailsCtrl', [
  '$scope', '$controller', '$window', '$routeParams', '$location', '$timeout', 'security', 'platform', 'earnings', 'withdrawals',
  ($scope, $controller, $window, $routeParams, $location, $timeout, security, platform, earnings, withdrawals) ->
    'use strict'

    return unless security.isAuthenticated()

    $controller 'DatepickerCtrl', {
      $scope: $scope
      $window: $window
      $routeParams: $routeParams
      $location: $location
    }

    $timeout ( -> $scope.balance = security.currentUser.balance), 500
    $scope.platform = platform
    $scope.stats = earnings.stats
    $scope.totalEarned = earnings.total
    $scope.withdrawals = withdrawals.withdrawals
    $scope.totalWithdrawals = withdrawals.total
    $scope.changeFinanceDetailPeriod = -> $scope.changeDetailPeriod '/finance'
    $scope.goToWallets = -> $location.path '/finance/wallet'
]
