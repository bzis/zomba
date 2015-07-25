angular.module('publisher').controller 'WithdrawalCtrl', [
  '$scope', '$routeParams', 'APP.CONFIG', 'security', 'ProgressBar', 'Utility', 'Withdrawals', 'wallets',
  ($scope, $routeParams, config, security, ProgressBar, Utility, Withdrawals, wallets) ->
    'use strict'

    return unless security.isAuthenticated()

    $scope.wallets = wallets
    $scope.wallet = wallets[0]
    $scope.amount = 0
    $scope.validationErrors = []
    $scope.completed = false

    for w in wallets
      $scope.wallet = w if w.id is parseInt($routeParams.walletId, 10)

    $scope.changeWallet = (wallet) -> $scope.wallet = wallet
    $scope.alertClose = -> $scope.validationErrors = []
    $scope.withdrawal = ->
      ProgressBar.start()
      $scope.validationErrors = []

      if $scope.amount > security.currentUser.balance
        $scope.validationErrors.push "Вы не можете вывести сумму большую, \
                                      чем на вашем балансе"
        ProgressBar.stop()
        return
      else if $scope.amount < config.withdrawal.limit
        $scope.validationErrors.push "Вы не можете вывести сумму меньше \
                                      #{config.withdrawal.limit} рублей"
        ProgressBar.stop()
        return

      Withdrawals.create($scope.wallet.id, $scope.amount).then (response) ->
        security.currentUser.balance -= $scope.amount
        security.currentUser.balance = security.currentUser.balance.toFixed 2
        $scope.completed = true
      .catch (response) ->
        $scope.validationErrors = Utility.toErrorList response.data.errors
      .finally( -> ProgressBar.stop())
]
