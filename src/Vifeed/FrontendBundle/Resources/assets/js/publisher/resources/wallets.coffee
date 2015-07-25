angular.module('resources.wallets', ['zmbk.config', 'security']).factory 'Wallets', [
  '$http', '$q', 'APP.CONFIG', 'security',
  ($http, $q, config, security) ->
    'use strict'

    new class Wallets
      TYPE_YANDEX_MONEY: 'yandex'
      TYPE_WEB_MONEY: 'wm'
      TYPE_QIWI: 'qiwi'
      resourceUrl: "#{config.apiPath}/wallets"
      _types: []

      # Gets all possible wallet types
      allTypes: ->
        if @_types.length
          deferred = $q.defer()
          deferred.resolve @_types
          return deferred.promise
        $http.get(
          "#{config.apiPath}/wallet/types",
          headers: security.getAuthHeader()
        ).then (response) =>
          wallets = []
          for type, name of response.data
            switch type
              when @TYPE_YANDEX_MONEY then wallets.push getYandexMoney(type, name)
              when @TYPE_WEB_MONEY then wallets.push getWebMoney(type, name)
              when @TYPE_QIWI then wallets.push getQiwi(type, name)
          @_types = wallets
          wallets

      # Get a new wallet structure
      new: ->
        @allTypes().then (types) -> types[0]

      # Creates a new wallet
      # Full url: /api/wallets
      create: (wallet) ->
        walletStructure =
          wallet:
            type: wallet.type
            number: wallet.number
        $http.put(
          @resourceUrl,
          walletStructure,
          headers: security.getAuthHeader()
        ).then (response) -> response.data

      # Gets a list of a user's wallets
      # Full url: /api/wallets
      all: ->
        $http.get(
          @resourceUrl,
          headers: security.getAuthHeader()
        ).then (response) =>
          @allTypes().then (types) ->
            return [] unless response.data.length
            for wallet in response.data
              wallet.name = type.name for type in types when wallet.type is type.type
            response.data

      # Deletes a wallet by its id
      # Full url: /api/wallets/{id}
      delete: (id) ->
        url = "#{@resourceUrl}/#{id}"
        $http.delete url, headers: security.getAuthHeader()

      # private

      # Gets YandexMoney wallet structure
      getYandexMoney = (type, name) ->
        {
          id: null
          type: type
          number: ''
          name: name
          format: new RegExp(/^[0-9]{13,}$/)
          pattern: 'Номер Я.Деньги имеет формат 41001******** (13 или больше цифр)'
          hint: "Номер кошелька Я.Деньги должен содержать только цифры
                 и иметь формат 41001******** (от 13-ти цифр и больше)."
        }

      # Gets WebMoney wallet structure
      getWebMoney = (type, name) ->
        {
          id: null
          type: type
          number: ''
          name: name
          format: new RegExp(/^R[0-9]{12}$/)
          pattern: 'Номер WM имеет формат R************ (12 цифр)'
          hint: "Номер кошелька WebMoney должен содержать префикс R
                 и далее 12 цифр. Возможен вывод только на рублевые кошельки."
        }

      # Gets Qiwi wallet structure
      getQiwi = (type, name) ->
        {
          id: null
          type: type
          number: ''
          name: name
          format: new RegExp(/^[0-9]{10}$/)
          pattern: 'Номер Qiwi должен состоять из 10 цифр'
          hint: "Номер кошелька Qiwi должен содержать 10 цифр телефона
                 без кода страны (+7) или междугороднего кода (8)."
        }
]
