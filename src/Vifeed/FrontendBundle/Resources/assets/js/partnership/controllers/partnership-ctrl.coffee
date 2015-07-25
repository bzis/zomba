angular.module('partnership').controller 'PartnershipCtrl', [
  '$scope', 'Utility', 'Partnerships',
  ($scope, Utility, Partnerships) ->
    'use strict'

    $scope.isSent = false
    $scope.validationErrors = []
    $scope.profile =
      firstName: ''
      lastName: ''
      email: ''
      phone: ''

    $scope.sendPartnershipRequest = ->
      $scope.validationErrors = []
      Partnerships.create($scope.profile).then(((response) ->
        $scope.isSent = true
      ), (response) ->
        $scope.validationErrors = Utility.toErrorList response.data.errors
        $scope.alertClose = -> $scope.validationErrors = []
      )
]
