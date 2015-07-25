angular.module('advertiser').controller 'FinanceDetailsCtrl', [
  '$scope', '$controller', '$window', '$routeParams', '$location', '$timeout', 'security', 'spendings', 'payments',
  ($scope, $controller, $window, $routeParams, $location, $timeout, security, spendings, payments) ->
    'use strict'

    return unless security.isAuthenticated()

    $controller 'DatepickerCtrl', {
      $scope: $scope
      $window: $window
      $routeParams: $routeParams
      $location: $location
    }

    $timeout ( -> $scope.balance = security.currentUser.balance), 500

    $scope.stats = spendings.stats
    $scope.totalSpendings =
      views: spendings.total_views
      paidViews: spendings.total_paid_views
      charged: spendings.total_charged
      kpi: spendings.total_kpi
    $scope.payments = payments.payments
    $scope.totalPayments = payments.total

    $scope.changeFinanceDetailPeriod = -> $scope.changeDetailPeriod '/finance'
    $scope.replenishAccount = -> $location.path '/finance/replenishment'
]
