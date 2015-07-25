angular.module('partnership', ['resources.partnerships', 'kernel']).config [
  '$routeProvider', ($routeProvider) ->
    'use strict'

    $routeProvider
      .when('/partnership',
        templateUrl: '/bundles/vifeedfrontend/partials/partnership/form.html'
        controller: 'PartnershipCtrl'
      ).otherwise redirectTo: '/partnership'
]
