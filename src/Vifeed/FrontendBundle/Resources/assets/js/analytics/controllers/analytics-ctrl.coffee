angular.module('analytics').controller 'AnalyticsCtrl', [
  '$scope', '$sce', '$timeout', '$controller', '$window', '$routeParams', '$location', \
  'security', 'GoogleMapSettings', 'Statistics', 'ProgressBar', 'analytics',
  ($scope, $sce, $timeout, $ctrl, $window, $routeParams, $location, security, GoogleMapSettings, Statistics, ProgressBar, analytics) ->
    'use strict'

    return unless security.isAuthenticated()

    COUNTRY_LIMIT = 5

    # Inheritance from BaseAnalyticsCtrl
    $ctrl 'BaseAnalyticsCtrl', {
      $scope: $scope
      $sce: $sce
      $timeout: $timeout
      $controller: $ctrl
      $window: $window
      $routeParams: $routeParams
      $location: $location
      security: security
      GoogleMapSettings: GoogleMapSettings
      analytics: analytics
    }

    $scope.totalViews = analytics.views.total
    $scope.countries = if analytics.views.countries.length > COUNTRY_LIMIT
      $scope.countryFullList = analytics.views.countries
      analytics.views.countries.slice 0, COUNTRY_LIMIT
    else
      $scope.countryFullList = []
      analytics.views.countries
    $scope.cities = {}
    $scope.goToRealTime = ->
      $location.path "/analytics/#{$scope.chosenCampaign.id}/real-time"

    $scope.hideCountryDetails = ->
      $scope.selectedCountryId = 0
      $scope.selectedCountryName = null
      $scope.showCountryList = true

    $scope.showCountryDetails = (country) ->
      ProgressBar.start()
      countryId = country.country_id
      $scope.selectedCountryId = countryId
      $scope.selectedCountryName = country.name
      if $scope.cities.hasOwnProperty(countryId)
        ProgressBar.stop()
        $scope.showCountryList = false
        return
      Statistics.getCityGroupedByCountry($scope.chosenCampaign.id, countryId, $scope.period)
        .then (response) ->
          $scope.cities[countryId] = []
          for record in response
            unless record.city_id?
              record.city_id = 0
              record.name = 'Не определено'
            $scope.cities[countryId].push record
          $scope.showCountryList = false
        .catch (response) -> $scope.cities[countryId] = []
        .finally( -> ProgressBar.stop())

    $scope.expandCountryList = -> $scope.countries = $scope.countryFullList
    $scope.collapseCountryList = ->
      $scope.countries = $scope.countryFullList.slice 0, COUNTRY_LIMIT

    $scope.hideCountryDetails()
]
