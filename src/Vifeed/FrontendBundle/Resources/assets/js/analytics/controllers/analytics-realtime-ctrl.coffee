angular.module('analytics').controller 'AnalyticsRealtimeCtrl', [
  '$scope', '$sce', '$timeout', '$interval', '$controller', '$window', '$routeParams',
  '$location', 'security', 'APP.CONFIG', 'GoogleMapSettings',
  'ChartRealtimeConfigurator', 'RealtimeLoader', 'analytics',
  ($scope, $sce, $timeout, $interval, $ctrl, $wnd, $params, $location, security, \
  config, MapSettings, RealtimeChart, RealtimeLoader, analytics) ->
    'use strict'

    return unless security.isAuthenticated()

    # Inheritance from BaseAnalyticsCtrl
    $ctrl 'BaseAnalyticsCtrl', {
      $scope: $scope
      $sce: $sce
      $timeout: $timeout
      $controller: $ctrl
      $window: $wnd
      $routeParams: $params
      $location: $location
      security: security
      GoogleMapSettings: MapSettings
      analytics: analytics
    }

    $scope.chartConfig = RealtimeChart.getConfig()
    $scope.watchingNow = 0
    $scope.heatmapData = {}

    unless io?
      $scope.chartConfig.loading = false
      $scope.heatLayerCallback = (layer) -> null
      return

    $timeout ( ->
      RealtimeLoader.run(
        io.connect(config.statsPath, 'sync disconnect on unload': true, 'force new connection': true),
        $scope.chosenCampaign.id
      ).then (response) ->
        $scope.chartConfig.loading = false
        series = $scope.chartConfig.series
        now = $wnd.moment()
        series[0].data.push [now.valueOf(), response.vw]
        series[1].data.push [now.valueOf(), response.pvw]
        $scope.heatmapData = response.ct
        $scope.watchingNow = response.vw
    ), 0

    # TODO: Move the code below to an external service
    heatDataInterval = null
    $scope.heatLayerCallback = (layer) ->
      heatDataInterval = $interval ( ->
        data = []
        unless angular.equals {}, $scope.heatmapData
          for id, city of $scope.heatmapData
            views = parseInt(city.vw, 10)
            continue if views is 0 or parseInt(id, 10) is 0
            views += 1
            while views -= 1
              data.push new google.maps.LatLng(+city.lat, +city.lng)
        layer.setData new google.maps.MVCArray(data)
      ), 5000

    $scope.$on '$destroy', ->
      RealtimeLoader.stop()
      $interval.cancel heatDataInterval
]
