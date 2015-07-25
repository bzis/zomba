angular.module('advertiserApp', ['indexApp', 'advertiser']).run [
  '$window', ($window) ->
    'use strict'

    $window.moment.locale 'ru'
]
