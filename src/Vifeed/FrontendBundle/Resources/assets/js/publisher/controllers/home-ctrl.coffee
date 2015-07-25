angular.module('publisher').controller 'HomeCtrl', [
  '$scope', '$location', 'security', 'platforms',
  ($scope, $location, security, platforms) ->
    return unless security.isAuthenticated()

    if platforms.length is 0
      $location.path '/platform/new'
    else
      $location.path '/campaign/list'
]
