describe 'Billing', ->
  beforeEach( -> module 'resources.billing' )

  describe 'Resource', ->
    billing = {}
    $window = {}
    $httpBackend = {}
    mock = {}
    expect = chai.expect
    # timeout = {}

    beforeEach(inject (_$window_, _$httpBackend_, Billing) ->
      # timeout = $timeout
      $window = _$window_
      $httpBackend = _$httpBackend_
      billing = Billing
      mock = new BillingMock($httpBackend)
      mock.mockOkResponse()
    )

    it 'should have default resource url', ->
      expect(billing.resourceUrl).to.equal '/api/billing'

    it 'should return empty spendings if no data', ->
      reply = []
      period =
        startDate: $window.moment '2014-07-07'
        endDate: $window.moment '2014-08-07'
      billing.getSpendings(period).then (response) -> reply = response
      $httpBackend.flush()
      expect(reply).to.have.ownProperty 'campaigns'
      expect(reply).to.have.ownProperty 'total'
      expect(reply.campaigns).to.be.eql []
      expect(reply.total).to.be.eql 0

    it 'should return empty spendings for a campaign if no data', ->
      reply = []
      period =
        startDate: $window.moment '2014-07-07'
        endDate: $window.moment '2014-08-07'
      billing.getSpendingsByCampaignId(1, period).then (response) -> reply = response
      $httpBackend.flush()
      expect(reply).to.have.ownProperty 'stats'
      expect(reply).to.have.ownProperty 'total'
      expect(reply.stats).to.be.eql []
      expect(reply.total).to.be.eql 0

    it 'should return empty payments if no data', ->
      reply = []
      period =
        startDate: $window.moment '2014-07-07'
        endDate: $window.moment '2014-08-07'
      billing.getPayments(period).then (response) -> reply = response
      $httpBackend.flush()
      expect(reply).to.have.ownProperty 'payments'
      expect(reply).to.have.ownProperty 'total'
      expect(reply.payments).to.be.eql []
      expect(reply.total).to.be.eql 0

    it 'should return empty earnings if no data', ->
      reply = []
      period =
        startDate: $window.moment '2014-07-07'
        endDate: $window.moment '2014-08-07'
      billing.getEarnings(period).then (response) -> reply = response
      $httpBackend.flush()
      expect(reply).to.have.ownProperty 'platforms'
      expect(reply).to.have.ownProperty 'total'
      expect(reply.platforms).to.be.eql []
      expect(reply.total).to.be.eql 0

    it 'should return empty earnings for a platform if no data', ->
      reply = []
      period =
        startDate: $window.moment '2014-07-07'
        endDate: $window.moment '2014-08-07'
      billing.getEarningsByPlatformId(1, period).then (response) -> reply = response
      $httpBackend.flush()
      expect(reply).to.have.ownProperty 'stats'
      expect(reply).to.have.ownProperty 'total'
      expect(reply.stats).to.be.eql []
      expect(reply.total).to.be.eql 0

    it 'should return empty withdrawals if no data', ->
      reply = []
      period =
        startDate: $window.moment '2014-07-07'
        endDate: $window.moment '2014-08-07'
      billing.getWithdrawals(period).then (response) -> reply = response
      $httpBackend.flush()
      expect(reply).to.have.ownProperty 'withdrawals'
      expect(reply).to.have.ownProperty 'total'
      expect(reply.withdrawals).to.be.eql []
      expect(reply.total).to.be.eql 0

    it 'should throw an error if period is not passed to getSpendings/getPayments/getEarnings', ->
      errorFuncOne = -> billing.getSpendings()
      errorFuncTwo = -> billing.getPayments()
      errorFuncThree = -> billing.getEarnings()
      errorFuncFour = -> billing.getWithdrawals()
      expect(errorFuncOne).to.throw Error, /The period parameter must be an object/
      expect(errorFuncTwo).to.throw Error, /The period parameter must be an object/
      expect(errorFuncThree).to.throw Error, /The period parameter must be an object/
      expect(errorFuncFour).to.throw Error, /The period parameter must be an object/

    it 'should throw an error if period is an incorrect object', ->
      errorFuncOne = -> billing.getSpendings({})
      errorFuncTwo = -> billing.getPayments({})
      errorFuncThree = -> billing.getEarnings({})
      errorFuncFour = -> billing.getWithdrawals({})
      expect(errorFuncOne).to.throw Error, /must be an object and have startDate and endDate properties/
      expect(errorFuncTwo).to.throw Error, /must be an object and have startDate and endDate properties/
      expect(errorFuncThree).to.throw Error, /must be an object and have startDate and endDate properties/
      expect(errorFuncFour).to.throw Error, /must be an object and have startDate and endDate properties/
