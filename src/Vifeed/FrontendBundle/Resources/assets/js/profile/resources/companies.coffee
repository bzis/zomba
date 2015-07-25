angular.module('resources.companies', ['zmbk.config', 'security']).factory 'Companies', [
  '$http', 'APP.CONFIG', 'security',
  ($http, config, security) ->
    'use strict'

    new class Companies
      resourceUrl: "#{config.apiPath}/users/current/company"

      getTaxationSystems: ->
        [
          { name: 'ОСН' }
          { name: 'УСН' }
        ]

      new: ->
        {
          system: @getTaxationSystems()[0]
          name: ''
          address: ''
          inn: ''
          kpp: ''
          bic: ''
          bankAccount: ''
          correspondentAccount: ''
          contactName: ''
          position: ''
          phone: ''
          isApproved: false
        }

      getCurrentOrNew: ->
        @getCurrent().then (data) =>
          return @new() if data in ['', null]
          data

      getCurrent: ->
        $http.get(@resourceUrl, headers: security.getAuthHeader())
             .then (response) -> response.data

      create: (data) ->
        companyData =
          company:
            system: data.system
            name: data.name
            address: data.address
            contactName: data.contactName
            role: data.position
            phone: data.phone
            # additional data
        $http.put @resourceUrl, companyData, headers: security.getAuthHeader()
]
