angular.module('indexApp').controller 'PromoModalCtrl', [
  '$scope', '$modal',
  ($scope, $modal) ->
    'use strict'

    $scope.showPromoModal = ->
      modalInstance = $modal.open(
        template: '<iframe src="//player.vimeo.com/video/100289644?color=158073&amp;autoplay=1" width="1024" height="576" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
        windowTemplateUrl: '/bundles/vifeedfrontend/partials/modal/sexy-modal-window.html'
        controller: 'PromoManagementCtrl'
        size: 'lg'
      )
]
