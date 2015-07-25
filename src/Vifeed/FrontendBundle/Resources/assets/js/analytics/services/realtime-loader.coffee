angular.module('analytics').factory 'RealtimeLoader', [
  '$interval', 'socketFactory', ($interval, socketFactory) ->
    new class RealtimeLoader
      factory = null

      run: (socket, campaignId) ->
        factory = socketFactory
          prefix: '',
          ioSocket: socket
        factory.emit 'get statistics', campaignId if factory?
        @

      then: (callback) ->
        throw new Error 'You must call "run" method at first' unless factory?
        factory.on 'got statistics', callback

      stop: (callback = null) ->
        factory.disconnect callback if factory?
]
