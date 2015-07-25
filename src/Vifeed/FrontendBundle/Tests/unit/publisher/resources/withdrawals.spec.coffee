describe 'Withdrawals', ->
  beforeEach( -> module 'resources.withdrawals' )

  describe 'Resource', ->
    withdrawals = {}
    # $httpBackend = {}
    expect = chai.expect

    beforeEach(inject (_$httpBackend_, Withdrawals) ->
      # $httpBackend = _$httpBackend_
      withdrawals = Withdrawals
    )

    it 'should have default resource url', ->
      expect(withdrawals.resourceUrl).to.equal '/api/withdrawal'

    # it 'should have 3 constants', ->
    #   expect(wallets.TYPE_YANDEX_MONEY).to.equal 'yandex'
    #   expect(wallets.TYPE_WEB_MONEY).to.equal 'wm'
    #   expect(wallets.TYPE_QIWI).to.equal 'qiwi'

    # it 'should have Ya.Money as default wallet', ->
    #   wallet = wallets.new()
    #   expect(wallet).to.have.ownProperty 'type'
    #   expect(wallet.type).to.be.equal 'yandex'

    # it 'should return list of wallets', ->
    #   reply = []
    #   wallets.all().then (response) -> reply = response
    #   httpBackend.flush()
    #   expect(reply).to.have.length 3
    #   expect(reply[0]).to.have.ownProperty 'name'
    #   expect(reply[0].name).to.be.equal 'Qiwi'
    #   expect(reply[1]).to.have.ownProperty 'name'
    #   expect(reply[1].name).to.be.equal 'WebMoney'
    #   expect(reply[2]).to.have.ownProperty 'name'
    #   expect(reply[2].name).to.be.equal 'Яндекс.Деньги'
