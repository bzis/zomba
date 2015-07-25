angular.module('resources.withdrawals', ['zmbk.config', 'security']).factory 'Withdrawals', [
  '$http', 'APP.CONFIG', 'security', ($http, config, security) ->
    'use strict'

    new class Withdrawals
      resourceUrl: "#{config.apiPath}/withdrawal"

      create: (walletId, amount) ->
        withdrawalData =
          withdrawal:
            wallet: walletId
            amount: amount
        $http.put(
          @resourceUrl,
          withdrawalData,
          headers: security.getAuthHeader()
        ).then (response) -> response.data
]
