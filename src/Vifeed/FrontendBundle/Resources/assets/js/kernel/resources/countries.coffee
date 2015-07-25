angular.module('resources.countries', []).factory 'Countries', [
  '$http', 'APP.CONFIG', 'security',
  ($http, config, security) ->
    'use strict'

    new class Countries
      resourceUrl: "#{config.apiPath}/countries"

      all: ->
        $http.get(@resourceUrl, headers: security.getAuthHeader()).then (response) ->
          response.data
]
