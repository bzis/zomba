angular.module('publisher').controller 'WalletCtrl', [
  '$scope', '$location', 'security', 'wallets',
  ($scope, $location, security, wallets) ->
    return unless security.isAuthenticated()

    unless wallets.length
      $location.path '/finance/wallet/new'
    else
      $location.path '/finance/wallet/list'
]
