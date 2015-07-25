angular.module('advertiser').controller 'NewCtrl', [
  '$scope', '$location', 'security', 'campaigns',
  ($scope, $location, security, campaigns) ->
    'use strict'

    return unless security.isAuthenticated()

    $scope.newCampaignLink = ''
    $scope.campaignList = []
    $scope.clonableCampaign = {}

    if campaigns?
      $scope.campaignList = campaigns
      $scope.clonableCampaign = $scope.campaignList[0]
    else
      $scope.isApiError = true

    $scope.goToCreateCampaign = ->
      matches = $scope.newCampaignLink.match /(watch\?v=|youtu\.be\/)([a-zA-Z0-9\_\-]{11}).*/
      if matches[2]? then $location.path "/campaign/create/#{matches[2]}"

    $scope.goToCloneCampaign = -> $location.path "/campaign/#{$scope.clonableCampaign.id}/clone"
]
