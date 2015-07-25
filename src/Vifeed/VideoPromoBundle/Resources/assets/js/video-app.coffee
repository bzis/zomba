angular.module('videoApp', ['ngSanitize', 'toHtml', 'youtubeFetcher']).config ['$interpolateProvider', ($interpolateProvider) ->
  'use strict'

  $interpolateProvider.startSymbol '{[{'
  $interpolateProvider.endSymbol '}]}'
]
