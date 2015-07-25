angular.module('advertiser').controller 'ListCtrl', [
  '$scope', '$sce', '$modal', 'Utility', 'ProgressBar', 'Campaigns', 'security', 'campaigns',
  ($scope, $sce, $modal, Utility, ProgressBar, Campaigns, security, campaigns) ->
    'use strict'

    return unless security.isAuthenticated()

    $scope.isLoading = false
    $scope.statusErrors = []
    $scope.badges =
      on: 0
      ended: 0
      paused: 0
      archived: 0
    $scope.campaigns =
      on: []
      ended: []
      paused: []
      archived: []

    if campaigns?
      for campaign in campaigns
        campaign.status = 'paused' if campaign.status is 'awaiting'
        $scope.badges[campaign.status] += 1
        $scope.campaigns[campaign.status].push campaign
    else
      $scope.isApiError = true

    updateCampaign = (campaign, newStatus) ->
      ProgressBar.start()
      $scope.isLoading = true
      $scope.statusErrors = []

      Campaigns.updateStatus(campaign.id, newStatus).then ( ->
        $scope.badges[campaign.status] -= 1
        $scope.badges[newStatus] += 1
        for object, i in $scope.campaigns[campaign.status] when object.id is campaign.id
          $scope.campaigns[campaign.status].splice(i, 1)
          break
        campaign.status = newStatus
        if newStatus is 'archived'
          security.currentUser.balance += campaign.balance
          security.currentUser.balance = security.currentUser.balance.toFixed 2
          campaign.balance = 0
        else if newStatus is 'on' and campaign.balance is 0
          campaign.balance = campaign.totalBudget
          security.currentUser.balance -= campaign.totalBudget
          security.currentUser.balance = security.currentUser.balance.toFixed 2
        $scope.campaigns[newStatus].push campaign
      ), (response) ->
        $scope.statusErrors = Utility.toErrorList response.data.errors
        $scope.alertClose = -> $scope.statusErrors = []
      .finally ( ->
        $scope.isLoading = false
        ProgressBar.stop()
      )

    $scope.archiveCampaign = (campaign) -> updateCampaign campaign, 'archived'
    $scope.pauseCampaign = (campaign) -> updateCampaign campaign, 'paused'
    $scope.startCampaign = (campaign) -> updateCampaign campaign, 'on'

    $scope.videoPreview = (campaign, $event) ->
      $event.stopPropagation()
      previewUrl = $sce.trustAsResourceUrl "//www.youtube.com/embed/#{campaign.hash}"
      modalInstance = $modal.open {
        template: "<iframe frameborder='0' width='640' height='400' src='#{previewUrl}' allowfullscreen></iframe>"
        windowTemplateUrl: '/bundles/vifeedfrontend/partials/modal/sexy-modal-window.html'
        controller: 'CampaignVideoPreviewCtrl'
        size: 'lg'
      }
]
