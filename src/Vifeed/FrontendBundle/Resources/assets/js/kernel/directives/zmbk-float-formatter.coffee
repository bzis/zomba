angular.module('kernel').directive 'zmbkFloatFormatter', ->
  'use strict'

  {
    restrict: 'A'
    require: 'ngModel'
    link: (scope, element, attrs, modelCtrl) ->
      modelCtrl.$parsers.push (inputValue) ->
        return 0 unless inputValue?
        transformedInput = inputValue.replace /,/g, '.'
        return 0 if isNaN parseFloat(transformedInput)
        if transformedInput isnt inputValue
          modelCtrl.$setViewValue transformedInput
          modelCtrl.$render()
        transformedInput
  }
