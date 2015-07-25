angular.module('publisher').controller 'ListCtrl', [
  '$scope', '$route', 'security', 'Utility', 'ProgressBar', 'Platforms', 'platforms',
  ($scope, $route, security, Utility, ProgressBar, Platforms, platforms) ->
    'use strict'

    return unless security.isAuthenticated()

    $scope.platforms = platforms
    $scope.deletePlatform = (platform) ->
      if confirm("Вы действительно хотите удалить платформу '#{platform.name}'?")
        ProgressBar.start()
        Platforms.delete(platform.id).finally( ->
          ProgressBar.stop()
          $route.reload()
        )
]
