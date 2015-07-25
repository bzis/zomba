angular.module('publisher').controller 'CampaignListCtrl', [
  '$scope', '$sce', '$window', '$location', '$modal', 'security', 'ProgressBar', 'CampaignPaginator', 'Campaigns', 'response', 'countries',
  ($scope, $sce, $window, $location, $modal, security, ProgressBar, CampaignPaginator, Campaigns, response, countries) ->
    'use strict'

    return unless security.isAuthenticated()

    $scope.chosenPlatform = response.chosenPlatform
    $scope.chosenPlatform.name = 'Площадки' unless $scope.chosenPlatform.name
    $scope.platforms = response.platforms
    $scope.campaigns = response.campaigns
    $scope.paginator = response.paginator
    $scope.loadingStates = response.states
    $scope.chosenCountry =
      id: 0
      name: 'Все страны'
    $scope.countries = countries
    $scope.countries.unshift $scope.chosenCountry
    $scope.isLoading = false

    $scope.changeCampaignList = (platform, country, page) ->
      ProgressBar.start()
      $scope.isLoading = true

      CampaignPaginator.load({
        platformId: platform.id
        countryId: country.id
        page: page
      }).then (response) ->
        for campaign in response.campaigns
          campaign.isSelected = false
          $scope.loadingStates[campaign.id] = false
        $scope.campaigns = response.campaigns
        $scope.paginator = response.paginator
        $window.scrollTo 0, 0
      .finally( ->
        $scope.isLoading = false
        ProgressBar.stop()
      )

    $scope.loadCampaignsByPlatform = (platform) ->
      $scope.chosenPlatform.id = platform.id
      $scope.chosenPlatform.name = platform.name
      $scope.chosenPlatform.hashId = platform.hash_id
      $scope.campaigns = [] if $scope.campaigns.length
      $scope.changeCampaignList(platform, $scope.chosenCountry, 1)

    $scope.changeCountry = (platform, country) ->
      return unless platform.id?
      $scope.campaigns = [] if $scope.campaigns.length > 0
      $scope.chosenCountry = country
      countryId = if country.id is 0 then undefined else country.id
      $scope.changeCampaignList(platform, country, 1)

    $scope.highlightCampaign = (campaign) -> campaign.isSelected = true
    $scope.shadowCampaign = (campaign) -> campaign.isSelected = false

    $scope.enableCampaign = (platformId, campaign, $event) ->
      $event.stopPropagation()
      $scope.isLoading = true
      $scope.loadingStates[campaign.id] = true
      Campaigns.enableByPlatformId(campaign.id, platformId).then ->
        campaign.isBanned = false
      .finally( ->
        $scope.isLoading = false
        $scope.loadingStates[campaign.id] = false
      )

    $scope.disableCampaign = (platformId, campaign, $event) ->
      $event.stopPropagation()
      $scope.isLoading = true
      $scope.loadingStates[campaign.id] = true

      Campaigns.disableByPlatformId(campaign.id, platformId).then ->
        campaign.isBanned = true
      .finally( ->
        $scope.isLoading = false
        $scope.loadingStates[campaign.id] = false
      )

    $scope.goToWidget = (platformHashId) ->
      $location.path "/platform/#{platformHashId}/widget"

    $scope.goToCampaignWidget = (platformHashId, campaignId) ->
      $location.path "/platform/#{platformHashId}/widget/#{campaignId}"

    $scope.videoPreview = (campaign, $event) ->
      $event.stopPropagation()
      previewUrl = $sce.trustAsResourceUrl "//www.youtube.com/embed/#{campaign.hash}"
      modalInstance = $modal.open(
        template: "<iframe frameborder='0' width='640' height='400' src='#{previewUrl}' allowfullscreen></iframe>"
        windowTemplateUrl: '/bundles/vifeedfrontend/partials/modal/sexy-modal-window.html'
        controller: 'CampaignVideoPreviewCtrl'
        size: 'lg'
      )
]
