describe 'FormCtrl:', ->
  beforeEach( -> module 'advertiserApp' )

  describe 'Controller', ->
    scope = {}
    httpBackend = {}
    mock = {}
    campaign = {}
    CampaignsFactory = {}
    expect = chai.expect

    beforeEach(inject ($rootScope, $httpBackend, $controller, security, Campaigns) ->
      scope = $rootScope.$new()
      httpBackend = $httpBackend
      mock = new CampaignsMock(httpBackend)
      mock.mockOkResponse()
      campaign = Campaigns.new()
      CampaignsFactory = Campaigns
      security.currentUser.email = 'advertiser@mail.com'
      security.currentUser.token = 'ThisIsAToken'
      security.currentUser.type = 'advertiser'
      $controller 'FormCtrl', {
        $scope: scope
        security: security
        Campaigns: CampaignsFactory
        campaign: campaign
        countries: []
        ages: []
        isClone: false
      }
    )

    it 'should have a campaign with the infinite daily budget if it sets so', ->
      scope.unlimitedDailyBudget = true
      scope.saveCampaign()
      scope.$digest()
      expect(scope.campaign.dailyBudget).to.be.equal 0

    it 'should have a new campaign with the infinite daily budget', ->
      scope.$digest()
      expect(scope.unlimitedDailyBudget).to.be.true

    # TODO: rewrite this test
    it 'should have a loaded campaign with defined daily budget', ->
      CampaignsFactory.getById(1).then (cmpn) ->
        expect(cmpn.dailyBudget).to.be.equal 80
