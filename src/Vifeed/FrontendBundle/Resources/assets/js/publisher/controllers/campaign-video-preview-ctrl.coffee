angular.module('publisher').controller 'CampaignVideoPreviewCtrl', [
  '$scope', '$modalInstance',
  ($scope, $modalInstance) ->
    $scope.close = -> $modalInstance.close()
]
