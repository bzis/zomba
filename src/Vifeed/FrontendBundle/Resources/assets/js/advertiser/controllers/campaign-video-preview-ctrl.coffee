angular.module('advertiser').controller 'CampaignVideoPreviewCtrl', [
  '$scope', '$modalInstance',
  ($scope, $modalInstance) ->
    $scope.close = -> $modalInstance.close()
]
