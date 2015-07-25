angular.module('kernel').directive 'zmbkMax', ->
  'use strict'

  {
    restrict: 'A'
    require: 'ngModel'
    link: (scope, elem, attr, ctrl) ->
      scope.$watch attr.zmbkMax, -> ctrl.$setViewValue ctrl.$viewValue
      ctrl.$parsers.push (value) ->
        max = scope.$eval(attr.zmbkMax) || Infinity
        if value? and value > max
          ctrl.$setValidity 'max', false
        else
          ctrl.$setValidity 'max', true
        value
  }
