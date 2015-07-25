angular.module('indexApp').controller 'PromoManagementCtrl', [
  '$scope', '$modalInstance',
  ($scope, $modalInstance) ->
    $scope.close = -> $modalInstance.close()
]
