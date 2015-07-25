angular.module('kernel').directive 'zmbkRange', ->
  'use strict'

  {
    restrict: 'A'
    require: 'ngModel'
    link: (scope, elem, attr, ctrl) ->
      scope.$watch ctrl.$name, -> ctrl.$setViewValue ctrl.$viewValue
      validator = (value) ->
        min = scope.$eval(attr.min) || 0
        max = scope.$eval(attr.max) || Infinity
        if value? and (value < min or value > max)
          ctrl.$setValidity 'range', false
        else
          ctrl.$setValidity 'range', true
        value
      ctrl.$parsers.push validator
  }
