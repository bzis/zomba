angular.module('mixin.mixer', []).factory 'Mixer', ->
  'use strict'

  new class Mixer
    mixOf: (base, mixins...) ->
      class Mixed extends base
      # earlier mixins override later ones
      for mixin in mixins by -1
        for name, method of mixin::
          Mixed::[name] = method
      Mixed
