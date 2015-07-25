angular.module('analytics').controller 'BaseAnalyticsCtrl', [
  '$scope', '$sce', '$timeout', '$controller', '$window', '$routeParams', '$location', \
  '$modal', 'security', 'GoogleMapSettings', 'analytics',
  ($scope, $sce, $timeout, $ctrl, $wnd, $params, $location, $modal, security, Chart, analytics) ->
    'use strict'

    return unless security.isAuthenticated()
    unless analytics.campaigns?.current?
      $location.path '/analytics'
      return

    google = $wnd.google
    moment = $wnd.moment
    Highcharts.setOptions global: useUTC: false

    # Inheritance from DatepickerCtrl
    $ctrl 'DatepickerCtrl', {
      $scope: $scope
      $window: $wnd
      $routeParams: $params
      $location: $location
    }

    $scope.campaigns = analytics.campaigns.list
    $scope.chosenCampaign = analytics.campaigns.current
    $scope.chartConfig = analytics.configs.chart
    $scope.map = Chart.getMapSettings()
    $scope.heatmap = Chart.getHeatMapSettings()

    $scope.heatLayerCallback = (layer) ->
      data = []
      for record in analytics.views.heatmap
        views = record.views + 1
        while views -= 1
          data.push new google.maps.LatLng(+record.latitude, +record.longitude)
      layer.setData new google.maps.MVCArray(data)

    $scope.changeCampaign = (campaign) ->
      $scope.changePeriod "/analytics/#{campaign.id}"

    $scope.changeAnalyticsPeriod = ->
      $scope.changePeriod "/analytics/#{$scope.chosenCampaign.id}"

    $scope.videoPreview = (campaign, $event) ->
      $event.stopPropagation()
      previewUrl = $sce.trustAsResourceUrl "//www.youtube.com/embed/#{campaign.hash}"
      modalInstance = $modal.open(
        template: "<iframe frameborder='0' width='640' height='400' src='#{previewUrl}' allowfullscreen></iframe>"
        windowTemplateUrl: '/bundles/vifeedfrontend/partials/modal/sexy-modal-window.html'
        controller: 'AnalyticsVideoPreviewCtrl'
        size: 'lg'
      )

    # Redraw the chart
    $scope.$on '$viewContentLoaded', ->
      $timeout ( -> $scope.$broadcast 'highchartsng.reflow'), 100
]
