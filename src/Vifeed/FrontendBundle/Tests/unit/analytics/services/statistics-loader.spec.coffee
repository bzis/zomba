describe 'StatisticsLoader', ->
  'use strict'

  beforeEach( -> module 'analytics' )

  describe 'Service', ->
    loader = {}
    scope = {}
    http = {}
    statsMock = {}
    moment = {}
    period = {}
    campaignList = []
    expect = chai.expect

    beforeEach(inject ($rootScope, $window, $httpBackend, StatisticsLoader) ->
      scope = $rootScope
      http = $httpBackend
      campaignMock = new CampaignsMock(http)
      statsMock = new StatisticsMock(http)
      campaignList = campaignMock.getCampaignList()
      loader = StatisticsLoader

      moment = $window.moment
      period =
        startDate: moment('2014-05-10').subtract(7, 'days').startOf('day'),
        endDate: moment('2014-05-10').startOf('day')
    )

    it 'should return an object with no data when campaign list is empty', ->
      data =
        campaigns:
          current: {}
          list: []
        configs:
          chart: {}
        views:
          heatmap: []
          countries: []
          cities: {}
          total: 0
      loader.load(period).then (response) -> expect(response).to.eql data
      scope.$apply()

    it 'should return a data object when only campaign list is set', ->
      statsMock.mockDataResponseForLastCampaign()
      response = null
      loader.load(period, campaignList).then (reply) -> response = reply
      http.flush()
      expect(response.campaigns.list).to.eql campaignList
      expect(response.campaigns.current).to.eql campaignList[3]
      expect(response.configs.chart).to.be.instanceOf Object
      expect(response.configs.chart).to.have.ownProperty 'options'
      expect(response.configs.chart).to.have.ownProperty 'series'
      expect(response.configs.chart.series).to.be.instanceof Array
      expect(response.configs.chart.series).to.have.length 2
      expect(response.configs.chart).to.have.ownProperty 'title'
      expect(response.configs.chart).to.have.ownProperty 'credits'
      expect(response.configs.chart).to.have.ownProperty 'loading'
      expect(response.views.heatmap).to.be.instanceOf Array
      expect(response.views.heatmap).to.have.length 1
      expect(response.views.countries).to.be.instanceOf Array
      expect(response.views.countries).to.have.length 2
      expect(response.views.total).to.eql 11
      expect(response.views.cities).to.eql {}

    it 'should return a data object when campaign list and campaign id are set', ->
      statsMock.mockDataResponseForFirstCampaign()
      response = null
      loader.load(period, campaignList, 1).then (reply) -> response = reply
      http.flush()
      expect(response.campaigns.list).to.eql campaignList
      expect(response.campaigns.current).to.eql campaignList[0]
      expect(response.configs.chart).to.be.instanceOf Object
      expect(response.configs.chart).to.have.ownProperty 'options'
      expect(response.configs.chart).to.have.ownProperty 'series'
      expect(response.configs.chart.series).to.be.instanceof Array
      expect(response.configs.chart.series).to.have.length 2
      expect(response.configs.chart).to.have.ownProperty 'title'
      expect(response.configs.chart).to.have.ownProperty 'credits'
      expect(response.configs.chart).to.have.ownProperty 'loading'
      expect(response.views.heatmap).to.be.instanceOf Array
      expect(response.views.heatmap).to.have.length 2
      expect(response.views.countries).to.be.instanceOf Array
      expect(response.views.countries).to.have.length 3
      expect(response.views.total).to.eql 5
      expect(response.views.cities).to.eql {}

    it 'should return zero totalViews when no stats data', ->
      statsMock.mockNoDataResponseForFirstCampaign()
      response = null
      loader.load(period, campaignList, 1).then (reply) -> response = reply
      http.flush()
      expect(response.views.total).to.equal 0

    it 'should return an object with no data when campaign id is not in the list', ->
      loader.load(period, campaignList, 1000).then (response) ->
        expect(response.campaigns.current).to.be.undefined
      scope.$apply()
