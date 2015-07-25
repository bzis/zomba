angular.module('mixin.validatorPeriod', []).factory 'ValidatorPeriodMixin', [ ->
  'use strict'

  class ValidatorPeriodMixin
    isValid: (period) ->
      period?.startDate? and period.endDate? and period.startDate.isValid()
]
