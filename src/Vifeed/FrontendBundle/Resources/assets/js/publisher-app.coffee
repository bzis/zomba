angular.module('publisherApp', ['indexApp', 'publisher']).run [
  '$window', ($window) ->
    'use strict'

    $window.moment.locale 'ru'
]
