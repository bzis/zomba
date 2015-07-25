class WalletsMock
  constructor: (httpBackend) -> @http = httpBackend

  getWallets: ->
    [{"id":7,"type":"qiwi","number":"1234567890","withdrawnAmount":"0.00","lastOperationDate":null},
    {"id":14,"type":"wm","number":"R321321321123","withdrawnAmount":"0.00","lastOperationDate":null},
    {"id":1,"type":"yandex","number":"410011234567890","withdrawnAmount":"0.00","lastOperationDate":null}]

  getTypes: ->
    {"qiwi":"Qiwi","wm":"WebMoney","yandex":"\u042f\u043d\u0434\u0435\u043a\u0441.\u0414\u0435\u043d\u044c\u0433\u0438"}

  mockOkResponse: ->
    wallets = @getWallets()
    @http
      .when('GET', '/api/wallets')
      .respond (method, url, data, headers) -> [200, wallets, {}]
    types = @getTypes()
    @http
      .when('GET', '/api/wallet/types')
      .respond (method, url, data, headers) -> [200, types, {}]
