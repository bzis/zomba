angular.module('analytics').factory 'StatisticsLoader', [
  '$q', 'Statistics', 'ChartConfigurator',
  ($q, Statistics, ChartConfigurator) ->
    'use strict'

    new class StatisticsLoader
      data =
        campaigns:
          current: {}
          list: []
        configs:
          chart: {}
        views:
          heatmap: []
          countries: []
          cities: {}
          total: 0

      load: (period, campaigns = [], campaignId = null) ->
        return getDataPromise() unless campaigns.length
        chosenCampaign = detectChosenCampaign campaigns, campaignId
        return getDataPromise() unless chosenCampaign
        chartPromise = getChartPromise chosenCampaign.id, period
        heatPromise = getHeatmapPromise chosenCampaign.id, period
        countryPromise = getCountryPromise chosenCampaign.id, period
        $q.all([chartPromise, heatPromise, countryPromise]).then -> data

      # private

      getDataPromise = ->
        deferred = $q.defer()
        deferred.resolve data
        deferred.promise

      detectChosenCampaign = (campaigns, campaignId = null) ->
        data.campaigns.list = campaigns
        unless campaignId?
          chosenCampaign = campaigns[-1..][0]
        else
          chosenCampaign = campaign for campaign in campaigns when campaign.id is parseInt(campaignId, 10)
        data.campaigns.current = chosenCampaign
        chosenCampaign

      getChartPromise = (campaignId, period) ->
        period.startDate.startOf('day')
        period.endDate.startOf('day')
        dayLabel = getDayLabel period
        if dayLabel?
          Statistics.getHourly(campaignId, dayLabel).then (records) ->
            reckonTotalViews records
            data.configs.chart = ChartConfigurator.getConfig {
              hourly: true
              period: period
              records: records
              zoomLevel: 1
            }
        else
          Statistics.getDaily(campaignId, period).then (records) ->
            reckonTotalViews records
            data.configs.chart = ChartConfigurator.getConfig {
              period: period
              records: records
              zoomLevel: 7
            }

      reckonTotalViews = (records = []) ->
        data.views.total = 0
        return unless records.length
        for record in records
          data.views.total += parseInt(record.paid_views, 10)

      getDayLabel = (period) ->
        from = period.startDate.valueOf()
        till = period.endDate.valueOf()
        today = moment().startOf('day').valueOf()
        yesterday = moment().subtract(1, 'day').startOf('day').valueOf()
        if from is till
          switch from
            when today then 'today'
            when yesterday then 'yesterday'
            else null

      getHeatmapPromise = (campaignId, period) ->
        data.views.heatmap = []
        Statistics.getGroupedByCity(campaignId, period).then (response) ->
          data.views.heatmap.push record for record in response when record.name?

      getCountryPromise = (campaignId, period) ->
        data.views.countries = []
        Statistics.getGroupedByCountry(campaignId, period).then (response) ->
          for record in response
            unless record.country_id?
              record.country_id = 0
              record.name = 'Не определено'
            data.views.countries.push record
]
