angular.module('resources.ages', ['zmbk.config', 'security']).factory 'Ages', [
  '$http', 'APP.CONFIG', 'security',
  ($http, config, security) ->
    'use strict'

    new class Ages
      resourceUrl: "#{config.apiPath}/ageranges"

      # Gets all predenifed age ranges
      all: ->
        $http.get(@resourceUrl, headers: security.getAuthHeader())
        .then (response) -> response.data
]
