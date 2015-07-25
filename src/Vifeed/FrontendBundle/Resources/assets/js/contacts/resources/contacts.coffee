angular.module('resources.contacts', ['zmbk.config']).factory 'Contacts', [
  '$http', 'APP.CONFIG', ($http, config) ->
    'use strict'

    new class Contacts
      resourceUrl = "#{config.apiPath}/feedback"

      # Creates request to contacts
      # Full link: /api/feedback
      create: (contacts) ->
        request =
          feedback:
            name: contacts.name
            email: contacts.email
            phone: contacts.phone
            message: contacts.message
        $http.put resourceUrl, request
]
