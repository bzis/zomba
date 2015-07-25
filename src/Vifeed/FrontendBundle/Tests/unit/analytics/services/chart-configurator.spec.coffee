describe 'ChartConfigurator', ->
  'use strict'

  beforeEach( -> module 'analytics' )

  describe 'Service', ->
    config = {}
    expect = chai.expect

    beforeEach(inject (ChartConfigurator) -> config = ChartConfigurator)

    it 'should throw an error when period structure is not set', ->
      fn = -> config.getConfig({})
      expect(fn).to.throw /The property "period" is not set/

    it 'should throw an error when zoomLevel option is not set', ->
      fn = -> config.getConfig {
        period: startDate: '2014-01-01', endDate: '2014-01-10'
      }
      expect(fn).to.throw /The property "zoomLevel" is not set/

    it 'should throw an error when records option is not set', ->
      fn = -> config.getConfig {
        period: startDate: '2014-01-01', endDate: '2014-01-10'
        zoomLevel: 1
      }
      expect(fn).to.throw /The property "records" is not set/

    it 'should throw an error when records option is not an array', ->
      fn = -> config.getConfig {
        period: startDate: '2014-01-01', endDate: '2014-01-10'
        zoomLevel: 1
        records: {}
      }
      expect(fn).to.throw /The property "records" must be an array/

    it 'should return config structure when valid period in days is set', ->
      options = config.getConfig {
        period:
          startDate: '2014-01-01', endDate: '2014-01-03'
        zoomLevel: 1
        records: [
          {"views":"3291","paid_views":"2749","date":"2014-01-01"},
          {"views":"1020","paid_views":"858","date":"2014-01-02"},
          {"views":"128","paid_views":"107","date":"2014-01-03"}
        ]
      }
      expect(options).to.be.instanceof Object
      expect(options).to.have.ownProperty 'options'
      expect(options).to.have.ownProperty 'series'
      expect(options.series).to.be.instanceof Array
      expect(options.series).to.have.length 2
      expect(options.series[0]).to.be.eql {
        name: "Показы"
        pointStart: '2014-01-01'
        pointInterval: 24 * 3600000
        data: [['January 1, 2014', 3291], ['January 2, 2014', 1020], ['January 3, 2014', 128]]
      }
      expect(options.series[1]).to.be.eql {
        name: "Просмотры"
        pointStart: '2014-01-01'
        pointInterval: 24 * 3600000
        data: [['January 1, 2014', 2749], ['January 2, 2014', 858], ['January 3, 2014', 107]]
      }
      expect(options).to.have.ownProperty 'title'
      expect(options).to.have.ownProperty 'credits'
      expect(options).to.have.ownProperty 'loading'

    it 'should return config structure when valid period in hours is set', ->
      options = config.getConfig {
        hourly: true
        period:
          startDate: '2014-01-01', endDate: '2014-01-01'
        zoomLevel: 1
        records: [
          {"views":"3291","paid_views":"2749","hour":"1"},
          {"views":"1020","paid_views":"858","hour":"5"},
          {"views":"128","paid_views":"107","hour":"10"}
        ]
      }
      expect(options).to.be.instanceof Object
      expect(options).to.have.ownProperty 'options'
      expect(options).to.have.ownProperty 'series'
      expect(options.series).to.be.instanceof Array
      expect(options.series).to.have.length 2
      expect(options.series[0]).to.be.eql {
        name: "Показы"
        pointStart: '2014-01-01'
        pointInterval: 3600000
        data: [
          ['00:00', 0], ['01:00', 3291], ['02:00', 0]
          ['03:00', 0], ['04:00', 0], ['05:00', 1020]
          ['06:00', 0], ['07:00', 0], ['08:00', 0]
          ['09:00', 0], ['10:00', 128], ['11:00', 0]
          ['12:00', 0], ['13:00', 0], ['14:00', 0]
          ['15:00', 0], ['16:00', 0], ['17:00', 0]
          ['18:00', 0], ['19:00', 0], ['20:00', 0]
          ['21:00', 0], ['22:00', 0], ['23:00', 0]
        ]
      }
      expect(options.series[1]).to.be.eql {
        name: "Просмотры"
        pointStart: '2014-01-01'
        pointInterval: 3600000
        data: [
          ['00:00', 0], ['01:00', 2749], ['02:00', 0]
          ['03:00', 0], ['04:00', 0], ['05:00', 858]
          ['06:00', 0], ['07:00', 0], ['08:00', 0]
          ['09:00', 0], ['10:00', 107], ['11:00', 0]
          ['12:00', 0], ['13:00', 0], ['14:00', 0]
          ['15:00', 0], ['16:00', 0], ['17:00', 0]
          ['18:00', 0], ['19:00', 0], ['20:00', 0]
          ['21:00', 0], ['22:00', 0], ['23:00', 0]
        ]
      }
      expect(options).to.have.ownProperty 'title'
      expect(options).to.have.ownProperty 'credits'
      expect(options).to.have.ownProperty 'loading'
