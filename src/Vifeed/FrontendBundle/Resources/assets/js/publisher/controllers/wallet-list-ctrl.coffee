angular.module('publisher').controller 'WalletListCtrl', [
  '$scope', '$route', '$location', 'security', 'ProgressBar', 'Wallets',
  'wallets',
  ($scope, $route, $location, security, ProgressBar, Wallets, wallets) ->
    return unless security.isAuthenticated()

    $scope.wallets = wallets

    $scope.addWallet = -> $location.path '/finance/wallet/new'
    $scope.withdrawal = (wallet) ->
                        $location.path "/finance/withdrawal/#{wallet.id}"

    $scope.deleteWallet = (wallet) ->
      question = "Вы действительно хотите удалить кошелек
                  #{wallet.name} (#{wallet.number})?"

      if confirm(question)
        ProgressBar.start()
        Wallets.delete(wallet.id).finally(( ->
          ProgressBar.stop()
          $route.reload()))
]
