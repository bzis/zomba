angular.module('profile', ['resources.companies', 'templates-profile']).config [
  '$routeProvider', '$locationProvider',
  ($routeProvider, $locationProvider) ->
    'use strict'

    $routeProvider.when '/profile', {
      templateUrl: '/bundles/vifeedfrontend/partials/profile/profile.html'
      controller: 'ProfileCtrl'
      resolve:
        company: ['Companies', 'security', (Companies, security) ->
          Companies.getCurrentOrNew() if security.currentUser?.type is 'advertiser'
        ]
    }
]
