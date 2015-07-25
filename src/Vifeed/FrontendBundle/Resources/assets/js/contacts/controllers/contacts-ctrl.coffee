angular.module('contacts').controller 'ContactsCtrl', [
  '$scope', 'Utility', 'Contacts',
  ($scope, Utility, Contacts) ->
    'use strict'

    $scope.isSent = false
    $scope.validationErrors = []
    $scope.contacts =
      name: ''
      email: ''
      phone: ''
      message: ''

    $scope.sendMessage = ->
      $scope.validationErrors = []
      Contacts.create($scope.contacts).then ((response) ->
        $scope.isSent = true
      ), (response) ->
        $scope.validationErrors = Utility.toErrorList response.data.errors
        $scope.alertClose = -> $scope.validationErrors = []
]
