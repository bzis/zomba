angular.module('toHtml', []).filter 'toHtml', ->
  'use strict'

  (text) ->
    return '' if not text or text in ['null', 'undefined']
    text = text.replace /\n/g, '<br>'
    text = text.replace /\\/g, '<br>'
    text.replace /(https?:\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[A-Z0-9+&@#\/%=~_|])/ig,
                 '<a href="$1" target="_blank">$1</a>'
