describe 'GoogleMapSettings', ->
  'use strict'

  beforeEach( -> module 'analytics' )

  describe 'Service', ->
    options = {}
    expect = chai.expect

    beforeEach(inject (GoogleMapSettings) -> options = GoogleMapSettings)

    it 'should return map styles', ->
      expect(options.getMapStyles()).to.be.instanceof Array
      expect(options.getMapStyles()).to.have.length 11

    it 'should return map settings', ->
      expect(options.getMapSettings()).to.be.instanceof Object

    it 'should return heat map settings', ->
      expect(options.getHeatMapSettings()).to.be.instanceof Object
