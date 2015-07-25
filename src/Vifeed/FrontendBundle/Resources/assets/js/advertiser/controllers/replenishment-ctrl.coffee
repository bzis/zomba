angular.module('advertiser').controller 'ReplenishmentCtrl', [
  '$scope', '$window', 'security', 'ProgressBar', 'Orders', 'Companies', 'company',
  ($scope, $window, security, ProgressBar, Orders, Companies, company) ->
    'use strict'

    return unless security.isAuthenticated()

    $scope.showCompany = false
    $scope.order =
      amount: 0
      provider: 'robokassa'
      phone: ''
    $scope.taxationSystems = Companies.getTaxationSystems()
    $scope.company = company

    doReplenish = ->
      ProgressBar.start()
      Orders.create($scope.order).catch (response) ->
        unless response.status is 303
          $scope.isApiError = true
        else
          if $scope.order.provider is 'bank_receipt'
            $scope.bankReceiptGenerated = true
            $scope.bankAmount = $scope.order.amount
            $scope.bankOrderId = response.data.url.match(/\d+/)[0]
            $scope.order.amount = 0
          $window.location = response.data.url
      .finally( -> ProgressBar.stop())

    createCompany = -> console.log 'does nothing yet'

    $scope.hideCompanyForm = -> $scope.showCompany = false
    $scope.showCompanyForm = -> $scope.showCompany = !$scope.company.isApproved

    $scope.replenish = ->
      if $scope.showCompany
        createCompany()
        $scope.showCompany = false
      else
        doReplenish()
]
