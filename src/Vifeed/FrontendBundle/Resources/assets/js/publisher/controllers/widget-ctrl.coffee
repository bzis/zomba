angular.module('publisher').controller 'WidgetCtrl', [
  '$scope', '$sce', '$routeParams', '$location', '$window', 'APP.CONFIG', 'security', 'campaign',
  ($scope, $sce, $routeParams, $location, $window, config, security, campaign) ->
    'use strict'

    return unless security.isAuthenticated()

    VIDEO_CUSTOM_WIDTH = 800
    VIDEO_CUSTOM_HEIGHT = 500

    $scope.trackerHost = config.trackerHost
    $scope.widgetPath = "auto/#{$routeParams.platformHash}"
    $scope.previewUrl = null

    if campaign?
      $scope.widgetPath = "#{campaign.hashId}/#{$routeParams.platformHash}"
      $scope.previewUrl = $sce.trustAsResourceUrl "//www.youtube.com/embed/#{campaign.hash}"

    $scope.isNextButton = false
    $scope.nextParameter = '&nextBtn=1'
    $scope.shareNextParameter = ''
    $scope.videoCustomWidth = VIDEO_CUSTOM_WIDTH
    $scope.videoCustomHeight = VIDEO_CUSTOM_HEIGHT
    $scope.resolutionType = null
    $scope.resolutions =
      xs:
        width: 300, height: 200
      sm:
        width: 480, height: 310
      lm:
        width: 560, height: 360
      md:
        width: 640, height: 400
      cs:
        width: $scope.videoCustomWidth, height: $scope.videoCustomHeight

    drawVideoSizeParameter = ->
      $scope.videoSizeParameter =
        "width=#{$scope.resolutions[$scope.resolutionType].width}\
        &height=#{$scope.resolutions[$scope.resolutionType].height}"

    $scope.setResolution = (sizeKey) ->
      $scope.resolutionType = sizeKey
      drawVideoSizeParameter()

    $scope.refreshCustomResolution = ->
      unless angular.isNumber($scope.videoCustomWidth)
        $scope.videoCustomWidth = VIDEO_CUSTOM_WIDTH
      unless angular.isNumber($scope.videoCustomHeight)
        $scope.videoCustomHeight = VIDEO_CUSTOM_HEIGHT
      $scope.resolutions.cs.width = $scope.videoCustomWidth
      $scope.resolutions.cs.height = $scope.videoCustomHeight
      drawVideoSizeParameter()

    $scope.toggleNextButton = ->
      if $scope.isNextButton
        $scope.isNextButton = false
        $scope.nextParameter = ''
        $scope.shareNextParameter = '/0'
      else
        $scope.isNextButton = true
        $scope.nextParameter = '&nextBtn=1'
        $scope.shareNextParameter = ''
      $scope.shareLink = "http://#{config.videoHost}/#{$scope.widgetPath}\
                          #{$scope.shareNextParameter}"

    $scope.shareLinkVia = (type) ->
      switch type
        when 'gl'
          link = "https://plus.google.com/share?url=#{encodeURIComponent($scope.shareLink)}"
        when 'tw'
          link = "https://twitter.com/share?url=#{encodeURIComponent($scope.shareLink)}"
        when 'fb'
          link = "https://www.facebook.com/sharer/sharer.php?u=#{encodeURIComponent($scope.shareLink)}"
        when 'vk'
          link = "https://vk.com/share.php?url=#{encodeURIComponent($scope.shareLink)}"
        else
          link = ''
      # Wrap up window.open to scope function to avoid angular window error
      # see more https://github.com/angular/angular.js/issues/4853#issuecomment-28491586
      $scope.openWindow = ->
        $window.open link, '_blank'
        return true
      $scope.openWindow()

    # Initialize a state of the first page
    $scope.setResolution('md')
    $scope.toggleNextButton()
]
