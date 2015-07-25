angular.module('resources.partnerships', ['zmbk.config']).factory 'Partnerships', [
  '$http', 'APP.CONFIG', ($http, config) ->
    'use strict'

    new class Partnerships
      resourceUrl = "#{config.apiPath}/partnership"

      # Creates partnership request
      # Full link: /api/partnership
      create: (profile) ->
        request =
          partnership:
            name: "#{profile.firstName} #{profile.lastName}"
            email: profile.email
            phone: profile.phone
        $http.put resourceUrl, request
]
