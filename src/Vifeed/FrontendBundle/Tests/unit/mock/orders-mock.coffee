class OrdersMock
  constructor: (httpBackend) -> @http = httpBackend

  mockOkResponse: ->
    @http
      .when('GET', '/api/orders')
      .respond (method, url, data, headers) -> [200, {}, {}]

    orderRobokassa =
      order:
        amount: '10000'
      jms_choose_payment_method:
        method: 'robokassa'
    @http
      .when 'PUT', '/api/orders', orderRobokassa
      .respond (method, url, data, headers) -> [
        303,
        { url: 'http://test.robokassa.ru/ReturnResults.aspx?Culture=ru', orderId: 1 },
        {}
      ]

    orderBankReceipt =
      order:
        amount: '10000'
      jms_choose_payment_method:
        method: 'bank_receipt'
    @http
      .when 'PUT', '/api/orders', orderBankReceipt
      .respond (method, url, data, headers) -> [
        303,
        { url: '/api/orders/1/bill' },
        {}
      ]
