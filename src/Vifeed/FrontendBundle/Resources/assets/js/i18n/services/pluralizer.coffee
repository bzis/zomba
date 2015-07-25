angular.module('i18n.pluralizer', []).factory 'Pluralizer', ->
  new class Pluralizer
    ru: (number, one, many, other) ->
      return one if number % 10 == 1 && number % 100 != 11
      return many if number % 10 >= 2 && number % 10 <= 4 \
                  && (number % 100 < 10 || number % 100 >= 20)
      other
    en: -> console.log 'Realization required!'
