describe 'RealtimeLoader', ->
  'use strict'

  beforeEach( -> module 'analytics' )

  describe 'Service', ->
    timeout = {}
    loader = {}
    mockIoSocket = {}
    expect = chai.expect

    beforeEach(inject ($timeout, RealtimeLoader) ->
      timeout = $timeout
      loader = RealtimeLoader
      mockIoSocket = io.connect()
    )

    it 'should throw an error if called then without run', ->
      response = -> loader.then(->)
      expect(response).to.throw Error
      expect(response).to.throw /You must call "run" method at first/

    it 'should return data', ->
      cities = []
      cities[703] =
        lng: "-82.9588"
        lat: "39.8928"
        n_en: 'Columbus'
        n_ru: 'Колумбус'
        pvw: 101
        vw: 139
      data =
        'ct': cities,
        'vw': 139,
        'pvw': 101
      response = null
      loader.run mockIoSocket, 1
      loader.then (reply) -> response = reply
      mockIoSocket.emit 'got statistics', data
      timeout.flush()
      expect(response).to.eql data
