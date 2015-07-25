angular.module('analytics').factory 'ChartRealtimeConfigurator', [
  '$window', ($window) ->
    'use strict'

    new class ChartRealtimeConfigurator
      now = $window.moment()

      getConfig: ->
        options:
          chart:
            type: 'line'
            height: '300'
          yAxis:
            title:
              text: 'Количество показов и просмотров'
            min: 0
          xAxis:
            type: 'datetime'
          tooltip:
            shared: true
        series: [{
          name: 'Показы'
          pointStart: now.valueOf()
          pointInterval: 1000
          data: []
        }, {
          name: 'Просмотры'
          pointStart: now.valueOf()
          pointInterval: 1000
          data: []
        }]
        title:
          text: null
        credits:
          enabled: false
        loading: true
]
