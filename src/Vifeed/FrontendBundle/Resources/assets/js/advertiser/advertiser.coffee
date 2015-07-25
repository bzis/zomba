angular.module('advertiser', [
  'resources.ages',
  'resources.orders',
  'resources.campaigns',
  'resources.billing',
  'resources.countries',
  'resources.tags',
  'ui.select2',
  'youtubeFetcher',
  'bidOptimizer',
  'analytics',
  'datepicker',
  'profile',
  'templates-advertiser'
]).config [
  '$routeProvider', ($routeProvider) ->
    'use strict'

    $routeProvider
      .when '/', {
        template: ' '
        controller: 'HomeCtrl'
        resolve:
          campaigns: ['Campaigns', (Campaigns) -> Campaigns.all()]
      }
      .when '/campaign/new', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/new.html'
        controller: 'NewCtrl'
        resolve:
          campaigns: ['Campaigns', (Campaigns) -> Campaigns.all()]
      }
      .when '/campaign/create/:hash', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/form.html'
        controller: 'FormCtrl'
        resolve:
          campaign: ['$route', 'Campaigns', 'YoutubeFetcher', ($route, Campaigns, YoutubeFetcher) ->
            Campaigns.new($route.current.params.hash).then (campaign) ->
              YoutubeFetcher.fetch(campaign.hash).then (loadedData) ->
                campaign.title = loadedData.entry.title.$t
                campaign.description = loadedData.entry.media$group.media$description.$t.slice 0, 1020
                campaign.statistics =
                  uploaded: loadedData.entry.published.$t
                  duration: loadedData.entry.media$group.yt$duration.seconds
                  likes: loadedData.entry.yt$rating.numLikes
                  dislikes: loadedData.entry.yt$rating.numDislikes
                  rating: loadedData.entry.gd$rating.average
                  views: loadedData.entry.yt$statistics.viewCount
                  favorites: loadedData.entry.yt$statistics.favoriteCount
                  comments: loadedData.entry.gd$comments.gd$feedLink.countHint
              campaign
          ]
          countries: ['Countries', (Countries) -> Countries.all()]
          ages: ['Ages', (Ages) -> Ages.all()]
          isClone: -> false
      }
      .when '/campaign/:id/edit/', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/form.html'
        controller: 'FormCtrl'
        resolve:
          campaign: ['$route', 'Campaigns', ($route, Campaigns) ->
            Campaigns.getById $route.current.params.id
          ]
          countries: ['Countries', (Countries) -> Countries.all()]
          ages: ['Ages', (Ages) -> Ages.all()]
          isClone: -> false
      }
      .when '/campaign/:id/clone/', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/form.html'
        controller: 'FormCtrl'
        resolve:
          campaign: ['$route', 'Campaigns', ($route, Campaigns) ->
            Campaigns.getById $route.current.params.id
          ]
          countries: ['Countries', (Countries) -> Countries.all()]
          ages: ['Ages', (Ages) -> Ages.all()]
          isClone: -> true
      }
      .when '/campaign/management', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/list.html'
        controller: 'ListCtrl'
        resolve:
          campaigns: ['$location', 'Campaigns', ($location, Campaigns) ->
            Campaigns.all().then (campaigns) ->
              unless campaigns.length
                $location.path '/campaign/new'
                return
              campaigns
          ]
      }
      .when '/finance/replenishment', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/replenishment.html'
        controller: 'ReplenishmentCtrl'
        resolve:
          company: ['Companies', (Companies) -> Companies.getCurrentOrNew()]
      }
      .when '/finance/replenishment/completed', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/replenishment-completed.html'
        controller: 'ReplenishmentCompletedCtrl'
      }
      .when '/finance/:dateFrom?/:dateTo?', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/finance.html'
        controller: 'FinanceCtrl'
        resolve:
          spendings: ['$route', '$window', 'Billing', ($route, $window, Billing) ->
            period =
              startDate:
                if $route.current.params.dateFrom?
                  $window.moment $route.current.params.dateFrom
                else
                  $window.moment().subtract 1, 'month'
              endDate:
                if $route.current.params.dateTo?
                  $window.moment $route.current.params.dateTo
                else
                  $window.moment()
            Billing.getSpendings period
          ],
          payments: ['$route', '$window', 'Billing', ($route, $window, Billing) ->
            period =
              startDate:
                if $route.current.params.dateFrom?
                  $window.moment $route.current.params.dateFrom
                else
                  $window.moment().subtract 1, 'month'
              endDate:
                if $route.current.params.dateTo?
                  $window.moment $route.current.params.dateTo
                else
                  $window.moment()
            Billing.getPayments period
          ]
      }
      .when '/finance/:id/details/:dateFrom/:dateTo', {
        templateUrl: '/bundles/vifeedfrontend/partials/advertiser/finance-details.html'
        controller: 'FinanceDetailsCtrl'
        resolve:
          campaign: ['$route', 'Campaigns', ($route, Campaigns) ->
            Campaigns.getById $route.current.params.id
          ],
          spendings: ['$route', '$window', 'Billing', ($route, $window, Billing) ->
            period =
              startDate: $window.moment $route.current.params.dateFrom
              endDate: $window.moment $route.current.params.dateTo
            Billing.getSpendingsByCampaignId $route.current.params.id, period
          ],
          payments: ['$route', '$window', 'Billing', ($route, $window, Billing) ->
            period =
              startDate: $window.moment $route.current.params.dateFrom
              endDate: $window.moment $route.current.params.dateTo
            Billing.getPayments period
          ]
      }
      .otherwise redirectTo: '/'
]
