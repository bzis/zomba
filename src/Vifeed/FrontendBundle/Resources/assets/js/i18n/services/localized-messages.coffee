angular.module('i18n.localizedMessages', []).factory 'LocalizedMessages', [
  '$interpolate', 'MessagesRu', ($interpolate, i18nMessages) ->
    'use strict'

    handleNotFound = (msg, msgKey) ->  msg or "?#{msgKey}?"

    {
      get: (msgKey, interpolateParams) ->
        msg = i18nMessages[msgKey]
        return $interpolate(msg)(interpolateParams) if msg
        handleNotFound msg, msgKey
    }
]
