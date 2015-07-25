angular.module('analytics', [
  'ngRoute',
  'highcharts-ng',
  'resources.statistics',
  'google-maps',
  'templates-analytics',
  'btford.socket-io'
]).config ['$routeProvider', ($routeProvider) ->
  $routeProvider
    .when '/analytics/:campaignId/real-time', {
      templateUrl: '/bundles/vifeedfrontend/partials/analytics/analytics-realtime.html'
      controller: 'AnalyticsRealtimeCtrl'
      resolve:
        analytics: ['$route', 'Campaigns', ($route, Campaigns) ->
          campaignId = parseInt($route.current.params.campaignId, 10)
          Campaigns.allActive().then (campaignList) ->
            data =
              campaigns: list: campaignList, current: null
              configs: chart: {}
            data.campaigns.current = campaign for campaign in campaignList when campaign.id is campaignId
            data
        ]
    }
    .when '/analytics/:campaignId?/:dateFrom?/:dateTo?', {
      templateUrl: '/bundles/vifeedfrontend/partials/analytics/analytics.html'
      controller: 'AnalyticsCtrl'
      resolve:
        analytics: [
          '$window', '$route', 'Campaigns', 'StatisticsLoader',
          ($window, $route, Campaigns, StatisticsLoader) ->
            moment = $window.moment
            Campaigns.all().then (campaignList) ->
              if $route.current.params.dateFrom?
                dateFrom = moment($route.current.params.dateFrom).startOf('day')
              else
                dateFrom = moment().subtract(30, 'days').startOf('day')
              if $route.current.params.dateTo?
                dateTo = moment($route.current.params.dateTo).startOf('day')
              else
                dateTo = moment().startOf('day')
              period = startDate: dateFrom, endDate: dateTo
              StatisticsLoader.load period, campaignList, $route.current.params.campaignId
        ]
    }
]
