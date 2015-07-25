angular.module('contacts', ['resources.contacts', 'kernel']).config [
  '$routeProvider', ($routeProvider) ->
    'use strict'

    $routeProvider
      .when('/form',
        templateUrl: '/bundles/vifeedfrontend/partials/contacts/form.html'
        controller: 'ContactsCtrl'
      ).otherwise redirectTo: '/form'
]
