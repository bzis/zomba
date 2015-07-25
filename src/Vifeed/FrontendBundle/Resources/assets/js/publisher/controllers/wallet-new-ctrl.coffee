angular.module('publisher').controller 'WalletNewCtrl', [
  '$scope', '$location', 'security', 'ProgressBar', 'Utility', 'Wallets', 'wallets'
  ($scope, $location, security, ProgressBar, Utility, Wallets, wallets) ->
    return unless security.isAuthenticated()

    $scope.wallet = wallets[0]
    $scope.walletList = wallets

    $scope.changeWallet = (wallet) -> $scope.wallet = wallet
    $scope.createWallet = ->
      ProgressBar.start()
      Wallets.create($scope.wallet).then (response) ->
        $location.path '/finance/wallet/list'
      .finally( -> ProgressBar.stop())
]
