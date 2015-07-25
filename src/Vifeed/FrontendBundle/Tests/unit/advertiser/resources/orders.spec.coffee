describe 'Orders', ->
  beforeEach( -> module 'resources.orders' )

  describe 'Resource', ->
    orders = {}
    httpBackend = {}
    mock = {}
    expect = chai.expect

    beforeEach(inject ($httpBackend, Orders) ->
      httpBackend = $httpBackend
      orders = Orders
      mock = new OrdersMock(httpBackend)
      mock.mockOkResponse()
    )

    it 'should have default resource url', ->
      expect(orders.resourceUrl).to.equal '/api/orders'

    it 'should return url for Robokassa', ->
      order =
        amount: '10000'
        provider: 'robokassa'
      reply = null
      orders.create(order).catch (response) -> reply = response
      httpBackend.flush()
      expect(reply.data.url).to.eql 'http://test.robokassa.ru/ReturnResults.aspx?Culture=ru'

    it 'should return url for a bank receipt', ->
      order =
        amount: '10000'
        provider: 'bank_receipt'
      reply = null
      orders.create(order).catch (response) -> reply = response
      httpBackend.flush()
      expect(reply.data.url).to.eql '/api/orders/1/bill'
