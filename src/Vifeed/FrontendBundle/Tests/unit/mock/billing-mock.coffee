class BillingMock
  constructor: (httpBackend) -> @http = httpBackend

  getEmptySpendings: -> {"campaigns":[],"total":0}
  getEmptySpendingsByCampaign: -> {"stats":[],"total":0}
  getEmptyPayments: -> {"payments":[],"total":0}
  getEmptyEarnings: -> {"platforms":[],"total":0}
  getEmptyEarningsByPlatform: -> {"stats":[],"total":0}
  getEmptyWithdrawals: -> {"withdrawals":[],"total":0}

  mockOkResponse: ->
    emptySpendings = @getEmptySpendings()
    @http
      .when('GET', '/api/billing/spendings?date_from=2014-07-07&date_to=2014-08-07')
      .respond (method, url, data, headers) ->
        [200, emptySpendings, {}]
    emptySpendingsByCampaign = @getEmptySpendingsByCampaign()
    @http
      .when('GET', '/api/billing/spendings/1?date_from=2014-07-07&date_to=2014-08-07')
      .respond (method, url, data, headers) ->
        [200, emptySpendingsByCampaign, {}]
    emptyPayments = @getEmptyPayments()
    @http
      .when('GET', '/api/billing/payments?date_from=2014-07-07&date_to=2014-08-07')
      .respond (method, url, data, headers) ->
        [200, emptyPayments, {}]
    emptyEarnings = @getEmptyEarnings()
    @http
      .when('GET', '/api/billing/earnings?date_from=2014-07-07&date_to=2014-08-07')
      .respond (method, url, data, headers) ->
        [200, emptyEarnings, {}]
    emptyEarningsByPlatform = @getEmptyEarningsByPlatform()
    @http
      .when('GET', '/api/billing/earnings/1?date_from=2014-07-07&date_to=2014-08-07')
      .respond (method, url, data, headers) ->
        [200, emptyEarningsByPlatform, {}]
    emptyWithdrawals = @getEmptyWithdrawals()
    @http
      .when('GET', '/api/billing/withdrawals?date_from=2014-07-07&date_to=2014-08-07')
      .respond (method, url, data, headers) ->
        [200, emptyWithdrawals, {}]
