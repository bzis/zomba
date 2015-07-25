angular.module('analytics').controller 'AnalyticsVideoPreviewCtrl', [
  '$scope', '$modalInstance',
  ($scope, $modalInstance) ->
    'use strict'

    $scope.close = -> $modalInstance.close()
]
