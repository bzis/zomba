angular.module('kernel').directive 'zmbkMin', ->
  'use strict'

  {
    restrict: 'A'
    require: 'ngModel'
    link: (scope, elem, attr, ctrl) ->
      scope.$watch attr.zmbkMin, -> ctrl.$setViewValue ctrl.$viewValue
      ctrl.$parsers.push (value) ->
        min = scope.$eval(attr.zmbkMin) || 0
        if value? and value < min
          ctrl.$setValidity 'min', false
        else
          ctrl.$setValidity 'min', true
        value
  }
