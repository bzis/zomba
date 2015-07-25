angular.module('resources.billing', ['zmbk.config', 'security']).factory 'Billing', [
  '$http', '$window', 'APP.CONFIG', 'security',
  ($http, $window, config, security) ->
    'use strict'

    new class Billing
      resourceUrl: "#{config.apiPath}/billing"

      getSpendings: (period) -> callApi "#{@resourceUrl}/spendings", period
      getPayments: (period) -> callApi "#{@resourceUrl}/payments", period
      getEarnings: (period) -> callApi "#{@resourceUrl}/earnings", period
      getWithdrawals: (period) -> callApi "#{@resourceUrl}/withdrawals", period
      getSpendingsByCampaignId: (id, period) ->
        url = "#{@resourceUrl}/spendings/#{id}"
        callApi(url, period).then (spendings) ->
          for spending in spendings
            spending.date = $window.moment spending.date
          spendings
      getEarningsByPlatformId: (id, period) ->
        url = "#{@resourceUrl}/earnings/#{id}"
        callApi(url, period).then (earnings) ->
          for earning in earnings
            earning.date = $window.moment earning.date
          earnings

      # private

      callApi = (apiUrl, period) ->
        if not period?.startDate? or not period.endDate?
          throw new Error 'The period parameter must be an object and have startDate and endDate properties'
        url = "#{apiUrl}?date_from=#{period.startDate.format('YYYY-MM-DD')}\
              &date_to=#{period.endDate.format('YYYY-MM-DD')}"
        $http.get(url, headers: security.getAuthHeader()).then (response) -> response.data
]
