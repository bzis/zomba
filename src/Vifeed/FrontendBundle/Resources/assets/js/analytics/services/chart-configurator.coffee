angular.module('analytics').factory 'ChartConfigurator', [
  '$window', 'Statistics', ($window, Statistics) ->
    'use strict'

    new class ChartConfigurator
      HOUR_IN_SECS = 3600000
      HOURS_24 = 24
      moment = $window.moment
      highcharts = $window.Highcharts

      getConfig: (opts = {}) ->
        validateOptions opts
        config = {}
        if opts.hourly? and opts.hourly is true
          config =
            data: buildHourViews(opts.records)
            startDate: opts.period.startDate
            dayInterval: 1
        else
          config =
            data: buildDailyViews(opts.period, opts.records)
            startDate: opts.period.startDate
            dayInterval: HOURS_24
        getChartConfig(config, opts)

      # private

      validateOptions = (options) ->
        throw new Error 'The property "period" is not set' if not options.period?.startDate? or not options.period.endDate?
        throw new Error 'The property "zoomLevel" is not set' unless options.zoomLevel?
        throw new Error 'The property "records" is not set' unless options.records?
        throw new Error 'The property "records" must be an array' unless angular.isArray options.records

      getChartConfig = (config, opts) ->
        {
          options:
            chart:
              type: 'areaspline'
              zoomType: 'x'
              height: '300'
            subtitle:
              text: 'Нажмите на графике и перетащите границу участка, чтобы увидеть детальные данные'
            xAxis:
              type: 'datetime'
              maxZoom: opts.zoomLevel * HOURS_24 * HOUR_IN_SECS
              title:
                text: null
            yAxis:
              title:
                text: 'Количество показов и просмотров'
              min: 0
            tooltip:
              shared: true
            legend:
              enabled: false
            plotOptions:
              area:
                fillColor:
                  linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 }
                  stops: [
                    [0, highcharts.getOptions().colors[0]]
                    [1, highcharts.Color(highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                  ]
                lineWidth: 1
                marker:
                  enabled: false
                shadow: false
                states:
                  hover:
                    lineWidth: 1
                threshold: null
          series: getSeries(config)
          title:
            text: null
          credits:
            enabled: false
          loading: false
        }

      getSeries = (options) ->
        series = []
        series.push {
          name: "Показы"
          pointStart: options.startDate.valueOf()
          pointInterval: options.dayInterval * HOUR_IN_SECS
          data: options.data.views
        }, {
          name: "Просмотры",
          pointStart: options.startDate.valueOf()
          pointInterval: options.dayInterval * HOUR_IN_SECS
          data: options.data.paidViews
        }
        series

      buildDailyViews = (period, records = []) ->
        container = views: [], paidViews: []
        iterator = moment.twix(period.startDate.valueOf(), period.endDate.valueOf()).iterate 'days'
        while iterator.hasNext()
          current = iterator.next()
          data =
            views: [current.format('LL'), 0]
            paidViews: [current.format('LL'), 0]
          for record in records
            if record.date? and record.date is current.format 'YYYY-MM-DD'
              data.views = [current.format('LL'), parseInt(record.views, 10)]
              data.paidViews = [current.format('LL'), parseInt(record.paid_views, 10)]
          container.views.push data.views
          container.paidViews.push data.paidViews
        container

      buildHourViews = (records = []) ->
        container = views: [], paidViews: []
        for i in [0...HOURS_24]
          label = "0#{i}:00".slice(-5)
          data = views: [label, 0], paidViews: [label, 0]
          for record in records
            if record.hour? and parseInt(record.hour, 10) is i
              data.views = [label, parseInt(record.views, 10)]
              data.paidViews = [label, parseInt(record.paid_views, 10)]
          container.views.push data.views
          container.paidViews.push data.paidViews
        container
]
