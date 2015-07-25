describe 'Wallets', ->
  beforeEach( -> module 'resources.wallets' )

  describe 'Resource', ->
    wallets = {}
    httpBackend = {}
    mock = {}
    expect = chai.expect

    beforeEach(inject ($httpBackend, Wallets) ->
      httpBackend = $httpBackend
      wallets = Wallets
      mock = new WalletsMock(httpBackend)
      mock.mockOkResponse()
    )

    it 'should have default resource url', ->
      expect(wallets.resourceUrl).to.equal '/api/wallets'

    it 'should have 3 constants', ->
      expect(wallets.TYPE_YANDEX_MONEY).to.equal 'yandex'
      expect(wallets.TYPE_WEB_MONEY).to.equal 'wm'
      expect(wallets.TYPE_QIWI).to.equal 'qiwi'

    it 'should have Qiwi as default wallet', ->
      wallet = null
      wallets.new().then (response) -> wallet = response
      httpBackend.flush()
      expect(wallet).to.have.ownProperty 'id'
      expect(wallet).to.have.ownProperty 'type'
      expect(wallet).to.have.ownProperty 'number'
      expect(wallet).to.have.ownProperty 'name'
      expect(wallet).to.have.ownProperty 'format'
      expect(wallet).to.have.ownProperty 'pattern'
      expect(wallet).to.have.ownProperty 'hint'
      expect(wallet.id).to.be.equal null
      expect(wallet.type).to.be.equal 'qiwi'
      expect(wallet.number).to.be.equal ''
      expect(wallet.name).to.be.equal 'Qiwi'
      expect(wallet.pattern).to.contain 'Номер Qiwi'
      expect(wallet.hint).to.contain 'Номер кошелька Qiwi'

    it 'should return list of wallets', ->
      reply = []
      wallets.all().then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.have.length 3
      expect(reply[0]).to.have.ownProperty 'name'
      expect(reply[0].name).to.be.equal 'Qiwi'
      expect(reply[1]).to.have.ownProperty 'name'
      expect(reply[1].name).to.be.equal 'WebMoney'
      expect(reply[2]).to.have.ownProperty 'name'
      expect(reply[2].name).to.be.equal 'Яндекс.Деньги'
