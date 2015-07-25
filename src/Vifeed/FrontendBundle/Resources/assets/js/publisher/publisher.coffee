angular.module('publisher', [
  'kernel',
  'resources.billing',
  'resources.countries',
  'resources.tags',
  'resources.platforms',
  'resources.campaigns',
  'resources.vk',
  'resources.wallets',
  'resources.withdrawals',
  'datepicker',
  'profile',
  'ui.select2',
  'ui.bootstrap.modal',
  'templates-publisher'
]).config ['$routeProvider', ($routeProvider) ->
  'use strict'

  processWithdrawals = (Billing, Wallets, period) ->
    Billing.getWithdrawals(period).then (withdrawals) ->
      types = Wallets.allTypes()
      for withdrawal in withdrawals.withdrawals
        switch withdrawal.status
          when 'new'
            withdrawal.status = 'Принята'
          when 'error'
            withdrawal.status = 'Ошибка'
          when 'ok'
            withdrawal.status = 'Выполнена'
          else withdrawal.status = 'Ошибка'
        for type in types
          withdrawal.type = type.name if withdrawal.type is type.type
      withdrawals

  $routeProvider
    .when '/', {
      template: ' '
      controller: 'HomeCtrl'
      resolve:
        platforms: ['Platforms', (Platforms) -> Platforms.all()]
    }
    .when '/platform/new', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/new.html'
      controller: 'NewCtrl'
      resolve:
        countries: ['Countries', (Countries) -> Countries.all()]
    }
    .when '/platform/:platformHash/widget/:campaignId?', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/widget.html'
      controller: 'WidgetCtrl'
      resolve:
        campaign: ['$route', 'Campaigns', ($route, Campaigns) ->
          if $route.current.params.campaignId?
            return Campaigns.getById $route.current.params.campaignId
        ]
    }
    .when '/campaign/list', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/campaign-list.html'
      controller: 'CampaignListCtrl'
      resolve:
        response: ['Platforms', 'CampaignPaginator', (Platforms, CampaignPaginator) ->
          Platforms.all().then (platforms) ->
            data =
              platforms: []
              campaigns: []
              paginator: {}
              chosenPlatform:
                id: null
                name: null
              states: {}

            return data if platforms.length is 0

            data.platforms = platforms
            lastPlatform = platforms.slice(-1)[0]
            data.chosenPlatform =
              id: lastPlatform.id
              name: lastPlatform.name
              hashId: lastPlatform.hash_id

            CampaignPaginator.load(platformId: lastPlatform.id).then (response) ->
              for campaign in response.campaigns
                campaign.isSelected = false
                data.states[campaign.id] = false
              data.campaigns = response.campaigns
              data.paginator = response.paginator
              data
        ],
        countries: ['Countries', (Countries) -> Countries.all()]
    }
    .when '/platform/list', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/list.html'
      controller: 'ListCtrl'
      resolve:
        platforms: ['Platforms', (Platforms) -> Platforms.all()]
    }
    .when '/finance/wallet', {
      template: ' '
      controller: 'WalletCtrl'
      resolve:
        wallets: ['Wallets', (Wallets) -> Wallets.all()]
    }
    .when '/finance/wallet/list', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/wallet-list.html'
      controller: 'WalletListCtrl'
      resolve:
        wallets: ['Wallets', (Wallets) -> Wallets.all()]
    }
    .when '/finance/wallet/new', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/wallet-new.html'
      controller: 'WalletNewCtrl'
      resolve:
        wallets: ['Wallets', (Wallets) -> Wallets.allTypes()]
    }
    .when '/finance/withdrawal/:walletId?', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/withdrawal.html'
      controller: 'WithdrawalCtrl'
      resolve:
        wallets: ['Wallets', (Wallets) -> Wallets.all()]
    }
    .when '/finance/:dateFrom?/:dateTo?', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/finance.html'
      controller: 'FinanceCtrl'
      resolve:
        earnings: ['$route', '$window', 'Billing', ($route, $window, Billing) ->
          period =
            startDate:
              if $route.current.params.dateFrom?
                $window.moment($route.current.params.dateFrom)
              else
                $window.moment().subtract(1, 'month')
            endDate:
              if $route.current.params.dateTo?
                $window.moment($route.current.params.dateTo)
              else
                $window.moment()
          Billing.getEarnings period
        ],
        withdrawals: ['$route', '$window', 'Billing', 'Wallets', ($route, $window, Billing, Wallets) ->
          period =
            startDate:
              if $route.current.params.dateFrom?
                $window.moment($route.current.params.dateFrom)
              else
                $window.moment().subtract(1, 'month')
            endDate:
              if $route.current.params.dateTo?
                $window.moment($route.current.params.dateTo)
              else
                $window.moment()
          processWithdrawals Billing, Wallets, period
        ]
    }
    .when '/finance/:id/details/:dateFrom/:dateTo', {
      templateUrl: '/bundles/vifeedfrontend/partials/publisher/finance-details.html'
      controller: 'FinanceDetailsCtrl'
      resolve:
        platform: ['$route', 'Platforms', ($route, Platforms) ->
          Platforms.getById($route.current.params.id)
        ],
        earnings: ['$route', '$window', 'Billing', ($route, $window, Billing) ->
          period =
            startDate: $window.moment($route.current.params.dateFrom)
            endDate: $window.moment($route.current.params.dateTo)
          Billing.getEarningsByPlatformId $route.current.params.id, period
        ],
        withdrawals: ['$route', '$window', 'Billing', 'Wallets', ($route, $window, Billing, Wallets) ->
          period =
            startDate: $window.moment($route.current.params.dateFrom)
            endDate: $window.moment($route.current.params.dateTo)
          processWithdrawals Billing, Wallets, period
        ]
    }
    .otherwise redirectTo: '/'
]
