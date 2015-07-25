describe 'Campaigns', ->
  beforeEach( -> module 'resources.campaigns' )

  describe 'Resource', ->
    campaigns = {}
    httpBackend = {}
    mock = {}
    expect = chai.expect
    timeout = {}

    beforeEach(inject ($timeout, $httpBackend, Campaigns) ->
      timeout = $timeout
      httpBackend = $httpBackend
      campaigns = Campaigns
      mock = new CampaignsMock(httpBackend)
      mock.mockOkResponse()
    )

    it 'should have default resource url', ->
      expect(campaigns.resourceUrl).to.equal '/api/campaigns'

    it 'should have platform url', ->
      expect(campaigns.platformUrl).to.equal '/api/platforms'

    it 'should have default data if new method called', ->
      data =
        id: null
        hash: null
        hashId: null
        title: ''
        description: ''
        countries: []
        tags: []
        gender: null
        ages: []
        balance: 0
        totalBudget: 1000
        dailyBudget: 100
        remainingAmount: 1000
        budgetRatio:
          left: 100
          right: 0
        maxBid: 2
        views: 1000
        status: ''
        statusHuman: 'не установлен'
        isBanned: false
        previewUrl: ''
        createdAt: null
        statistics:
          uploaded: null
          duration: 0
          likes: 0
          dislikes: 0
          rating: 0
          views: 0
          favorites: 0
          comments: 0
          updatedAt: null
        socialActivity:
          fb:
            likes: 0
            shares: 0
          vk:
            likes: 0
            shares: 0
          gplus:
            likes: 0
            shares: 0
          total:
            likes: 0
            shares: 0
          updatedAt: null
      expect(campaigns.new()).to.eql data

    it 'should return promise if a hash passed into new method', ->
      response = null
      timeout ( ->
        campaigns.new('s0m3h4sh').then (campaign) -> response = campaign
      ), 0
      timeout.flush()
      expect(response).to.have.ownProperty 'hash'
      expect(response.hash).to.be.equal 's0m3h4sh'

    it 'should return all campaigns if all method called', ->
      reply = []
      campaigns.all().then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.have.length 4

    it 'should return only active campaigns if allActive method called', ->
      reply = []
      campaigns.allActive().then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.have.length 2

    it 'should one campaign if getById method called', ->
      reply = null
      campaigns.getById(3).then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.be.instanceOf Object
      expect(reply).to.have.ownProperty 'id'
      expect(reply).to.have.ownProperty 'hash'
      expect(reply).to.have.ownProperty 'hashId'
      expect(reply).to.have.ownProperty 'title'
      expect(reply).to.have.ownProperty 'description'
      expect(reply).to.have.ownProperty 'countries'
      expect(reply).to.have.ownProperty 'tags'
      expect(reply).to.have.ownProperty 'gender'
      expect(reply).to.have.ownProperty 'ages'
      expect(reply).to.have.ownProperty 'totalBudget'
      expect(reply).to.have.ownProperty 'dailyBudget'
      expect(reply).to.have.ownProperty 'maxBid'
      expect(reply).to.have.ownProperty 'views'
      expect(reply).to.have.ownProperty 'paidViews'
      expect(reply).to.have.ownProperty 'status'
      expect(reply).to.have.ownProperty 'statusHuman'
      expect(reply).to.have.ownProperty 'isBanned'
      expect(reply).to.have.ownProperty 'previewUrl'
      expect(reply.id).to.be.equal 3
      expect(reply.hash).to.be.equal 'TqnA7vhsD4o'
      expect(reply.hashId).to.be.equal '3na1n6'
      expect(reply.title).to.be.equal 'Видео из This is Хорошо. Сова. Что это у тебя там?'
      expect(reply.description).to.be.null
