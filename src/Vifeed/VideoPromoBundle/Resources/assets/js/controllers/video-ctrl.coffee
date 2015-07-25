angular.module('videoApp').controller 'VideoCtrl', [
  '$scope', '$window', '$timeout', 'YoutubeFetcher',
  ($scope, $window, $timeout, YoutubeFetcher) ->
    'use strict'

    $timeout ( ->
      return unless $window.zmbkVideo.campaign?
      $scope.title = $window.zmbkVideo.campaign.name
      $scope.description = $window.zmbkVideo.campaign.desc
      YoutubeFetcher.fetch($window.zmbkVideo.campaign.hash).then (response) ->
        $scope.views = response.entry.yt$statistics.viewCount
        $scope.likes = response.entry.yt$rating.numLikes
        $scope.dislikes = response.entry.yt$rating.numDislikes
    ), 0
]
