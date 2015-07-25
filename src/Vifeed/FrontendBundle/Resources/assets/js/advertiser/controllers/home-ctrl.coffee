angular.module('advertiser').controller 'HomeCtrl', [
  '$scope', '$location', 'security', 'campaigns',
  ($scope, $location, security, campaigns) ->
    'use strict'

    return unless security.isAuthenticated()

    if campaigns.length is 0
      $location.path '/campaign/new'
    else
      $location.path '/campaign/management'
]
