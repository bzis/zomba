angular.module('publisher').controller 'NewCtrl', [
  '$scope', '$location', 'security', 'Utility', 'ProgressBar',
  'Platforms', 'Vk', 'Tags', 'countries',
  ($scope, $loc, security, Util, ProgressBar, Platforms, Vk, Tags, countries) ->
    return unless security.isAuthenticated()

    $scope.validationErrors = []
    $scope.isVkConfirmed = false
    $scope.confirmationError = null
    $scope.platform = Platforms.new()
    $scope.countries = countries
    $scope.tags = []
    $scope.tagOptions = Tags.getOptions(tags: -> $scope.tags)
    $scope.isLoadingError = true unless $scope.countries.length

    $scope.detectType = ->
      regex = /^https?:\/\/(www\.)?(vk\.com|vkontakte\.ru|vkontakte\.com)/
      if regex.test($scope.platform.url)
        $scope.platform.type = Platforms.TYPE_VK
      else
        $scope.platform.type = Platforms.TYPE_SITE

    # TODO: Write tests for this function
    $scope.loadTags = ($event) ->
      Tags.loadTagsByWord($event.val).then (tags) -> $scope.tags = tags

    $scope.confirmVkPublic = ->
      $scope.confirmationError = null
      Vk.confirmGroupAccess($scope.platform.url).then (response) ->
        $scope.isVkConfirmed = response.confirmed
        $scope.platform.vk_id = response.vk_id
      .catch (response) ->
        $scope.isVkConfirmed = response.confirmed
        $scope.confirmationError = "Не удалось подтвердить указанную
                                    группу VKontakte. #{response.message}"

    $scope.createPlatform = ->
      ProgressBar.start()
      if $scope.platform.type is Platforms.TYPE_VK and not $scope.isVkConfirmed
        $scope.validationErrors.push "Вы не подтвердили, что являетесь
                                      администратором указанной группы VKontakte"
        ProgressBar.stop()
        return
      Platforms.create($scope.platform).then (response) ->
        $loc.path "/platform/#{response.data.id}/widget"
      .catch (response) ->
        if response.status is 500
          $scope.isLoadingError = true
          return
        $scope.validationErrors = Util.toErrorList(response.data.errors)
        $scope.alertClose = -> $scope.validationErrors = []
      .finally( -> ProgressBar.stop())
]
