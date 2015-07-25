angular.module('resources.statistics', [
  'zmbk.config',
  'security',
  'mixin.mixer',
  'mixin.validatorPeriod'
]).factory 'Statistics', [
  '$http', 'APP.CONFIG', 'security', 'Mixer', 'ValidatorPeriodMixin'
  ($http, config, security, Mixer, ValidatorPeriodMixin) ->
    'use strict'

    new class Statistics extends Mixer.mixOf ValidatorPeriodMixin
      resourceUrl: "#{config.apiPath}/campaigns"

      # Gets daily statistics data
      # Full link: /api/campaigns/{id}/statistics/daily
      getDaily: (campaignId, period) ->
        url = "#{@resourceUrl}/#{campaignId}/statistics/daily"
        performApiCall.call @, url, period

      # Gets hourly statistics data
      # Full link: /api/campaigns/{id}/statistics/hourly/{day}
      # * day - possible values: today | yesterday
      getHourly: (campaignId, day) ->
        unless isDayValid day
          throw new Error "The day parameter has invalid value.
                          Possible values are 'today' and 'yesterday'"
        url = "#{@resourceUrl}/#{campaignId}/statistics/hourly/#{day}"
        $http.get(url, headers: security.getAuthHeader()).then(
          (response) -> response.data
        )

      # Gets statistics data grouped by a city
      # Full link: /api/campaigns/{id}/statistics/geo
      getGroupedByCity: (campaignId, period) ->
        url = "#{@resourceUrl}/#{campaignId}/statistics/geo"
        performApiCall.call @, url, period

      # Gets statistics data grouped by a country
      # Full link: /api/campaigns/{id}/statistics/geo/countries
      getGroupedByCountry: (campaignId, period) ->
        url = "#{@resourceUrl}/#{campaignId}/statistics/geo/countries"
        performApiCall.call @, url, period

      # Gets statistics data grouped by one specific country
      # Full link:
      #   /api/campaigns/{campaign_id}/statistics/geo/countries/{country_id}
      getCityGroupedByCountry: (campaignId, countryId, period) ->
        url = "#{@resourceUrl}/#{campaignId}/statistics/geo"
        url += "/countries/#{countryId}"
        performApiCall.call @, url, period

      # private

      # Performs a call to API
      performApiCall = (url, period) ->
        throw new Error 'The period object is invalid' unless @isValid period
        url += '?date_from=' + period.startDate.format('YYYY-MM-DD')
        url += '&date_to=' + period.endDate.format('YYYY-MM-DD')
        $http.get(url, headers: security.getAuthHeader()).then(
          (response) -> response.data
        )

      # Checks whether a day has correct value
      isDayValid = (day) -> day == 'today' || day == 'yesterday'
]
