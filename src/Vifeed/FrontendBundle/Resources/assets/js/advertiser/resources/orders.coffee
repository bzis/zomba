angular.module('resources.orders', ['zmbk.config', 'security']).factory 'Orders', [
  '$http', 'APP.CONFIG', 'security',
  ($http, config, security) ->
    'use strict'

    new class Orders
      resourceUrl: "#{config.apiPath}/orders"

      # Creates a new order to replenish a user's account
      # Full url: /api/orders
      create: (order) ->
        orderStructure =
          order:
            amount: order.amount
          jms_choose_payment_method:
            method: order.provider

        if order.provider == 'qiwi_wallet'
          orderStructure.jms_choose_payment_method[phone] =
            number: '+7' + order.phone

        $http.put(
          @resourceUrl,
          orderStructure,
          headers: security.getAuthHeader()
        ).then (response) -> response.data

      # loadInvoice: (url) ->
      #   $http.get url, headers: security.getAuthHeader()
      #   .then (response) ->

      # Gets info whether the order is completed
      # Full url: /api/orders/{id}/complete
      complete: (id) ->
        url = "#{@resourceUrl}/#{id}/complete"
        $http.get url, headers: security.getAuthHeader()
]
