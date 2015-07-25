describe 'Statistics', ->
  'use strict'

  beforeEach( -> module 'resources.statistics' )

  describe 'Resource', ->
    stats = {}
    http = {}
    mock = {}
    expect = chai.expect

    beforeEach(inject ($httpBackend, Statistics) ->
      http = $httpBackend
      stats = Statistics
      mock = new StatisticsMock(http)
      mock.mockOkResponse()
    )

    it 'should have default resource url', ->
      expect(stats.resourceUrl).to.equal '/api/campaigns'

    it 'should throw an error for daily stats if period is invalid', ->
      response = -> stats.getDaily 1, {}
      expect(response).to.throw Error
      expect(response).to.throw /period object is invalid/

    it 'should return daily stats by campaign id and period', ->
      response = null
      period =
        startDate: moment('2014-01-01'),
        endDate: moment('2014-01-10')
      stats.getDaily(1, period).then (reply) -> response = reply
      http.flush()
      expect(response).to.be.instanceof Array
      expect(response).to.have.length 2

    it 'should return empty array if no stats data', ->
      response = null
      period =
        startDate: moment('2014-01-01'),
        endDate: moment('2014-01-10')
      stats.getDaily(999, period).then (reply) -> response = reply
      http.flush()
      expect(response).to.eql []

    it 'should return an error for hourly stats if day is invalid', ->
      response = -> stats.getHourly 1, 'week'
      expect(response).to.throw Error
      expect(response).to.throw /day parameter has invalid value/

    it 'should return empty array if no hourly stats data for day', ->
      response = null
      stats.getHourly(999, 'today').then (reply) -> response = reply
      http.flush()
      expect(response).to.eql []

    it 'should return empty array if no hourly stats data for yesterday', ->
      response = null
      stats.getHourly(999, 'yesterday').then (reply) -> response = reply
      http.flush()
      expect(response).to.eql []

    it 'should return empty array if no data grouped by a city', ->
      response = null
      period =
        startDate: moment('2014-01-01'),
        endDate: moment('2014-01-10')
      stats.getGroupedByCity(999, period).then (reply) -> response = reply
      http.flush()
      expect(response).to.eql []

    it 'should return empty array if no data grouped by a country', ->
      response = null
      period =
        startDate: moment('2014-01-01'),
        endDate: moment('2014-01-10')
      stats.getGroupedByCountry(999, period).then (reply) -> response = reply
      http.flush()
      expect(response).to.eql []

    it 'should return empty array if no city data grouped by a country', ->
      response = null
      period =
        startDate: moment('2014-01-01'),
        endDate: moment('2014-01-10')
      stats.getCityGroupedByCountry(999, 999, period).then(
        (reply) -> response = reply
      )
      http.flush()
      expect(response).to.eql []
