angular.module('bidOptimizer', []).factory 'BidOptimizer', [
  '$http', ($http) ->
    'use strict'

    new class BidOptimizer
      get: (totalBudget, bid) ->
        priceList = []
        totalBudget = 0 unless totalBudget?
        budget = parseInt totalBudget, 10
        maxViews = Math.round budget / 10
        reach = 0
        rate = 0.01

        for i in [1..10]
          rate += 0.0085
          reach = Math.round rate * i * maxViews
          priceList.push {
            bid: i
            reach: reach
            dailyBudget: reach * i
          }

        priceList
]
