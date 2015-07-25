angular.module('advertiser').controller 'FormCtrl', [
  '$scope', '$location', '$sce', '$filter', 'APP.CONFIG', 'security', 'Utility', 'ProgressBar', 'Campaigns', 'Tags',
  'campaign', 'countries', 'ages', 'isClone',
  ($scope, $location, $sce, $filter, config, security, Utility, ProgressBar, Campaigns, Tags, campaign, countries, ages, isClone) ->
    'use strict'

    return unless security.isAuthenticated()

    isEdit = $location.path().indexOf('edit') >= 0
    $scope.countryOptions =
      multiple: true
      maximumInputLength: 16
      tokenSeparators: [',', ' ']
    $scope.tags = []
    $scope.tagOptions = Tags.getOptions(tags: -> $scope.tags)
    $scope.previewUrl = ''
    $scope.validationErrors = []
    $scope.saveButtonLabel = 'Создать кампанию'
    $scope.showCancelButton = false
    $scope.unlimitedDailyBudget = true
    $scope.isFormError = false
    $scope.isLocked = false

    if isEdit
      $scope.saveButtonLabel = 'Сохранить изменения'
      $scope.showCancelButton = true

    unless campaign?
      $scope.isLoadingError = true
    else
      $scope.campaign = campaign
      $scope.campaign.maxBid = campaign.maxBid.toFixed 2
      $scope.maxBid = campaign.totalBudget
      $scope.previewUrl = $sce.trustAsResourceUrl "//www.youtube.com/embed/#{campaign.hash}"
      if isEdit and parseInt($scope.campaign.dailyBudget, 10) > 0
        $scope.unlimitedDailyBudget = false

    if $scope.campaign.status in ['on', 'paused', 'awaiting'] and not isClone
      $scope.isLocked = true

    $scope.countries = countries
    $scope.ages = ages

    processApiError = (response) ->
      $scope.validationErrors = Utility.toErrorList response.data.errors
      $scope.alertClose = -> $scope.validationErrors = []

    calculateViews = ->
      $scope.campaign.views = parseInt($scope.campaign.totalBudget / $scope.campaign.maxBid, 10)

    createCampaign = ->
      ProgressBar.start()
      Campaigns.create($scope.campaign).then(
        ((response) -> $location.path '/'),
        processApiError
      ).finally( -> ProgressBar.stop())

    updateCampaign = ->
      ProgressBar.start()
      if $scope.isLocked
        promise = Campaigns.updateLocked($scope.campaign).then(
          ( -> $location.path '/'),
          processApiError
        )
      else
        promise = Campaigns.update($scope.campaign).then(
          ( -> $location.path '/'),
          processApiError
        )
      promise.finally( -> ProgressBar.stop())

    $scope.closeBubbleAlert = -> $scope.isFormError = false

    $scope.loadTags = ($event) ->
      Tags.loadTagsByWord($event.val).then (tags) ->
        $scope.tags = tags

    $scope.clearAges = -> $scope.campaign.ages = []

    $scope.updateAge = (ageId) ->
      index = $scope.campaign.ages.indexOf ageId
      if index >= 0
        $scope.campaign.ages.splice index, 1
      else
        $scope.campaign.ages.push ageId

    $scope.changeBugdetLimits = ->
      if $scope.unlimitedDailyBudget
        $scope.minDailyBudget = 0
        $scope.campaign.dailyBudget = 0
        $scope.maxBid = $scope.campaign.totalBudget
      else
        $scope.minDailyBudget = Math.ceil($scope.campaign.totalBudget * 0.10)
        if $scope.minDailyBudget < config.campaign.minDailyLimit
          $scope.minDailyBudget = config.campaign.minDailyLimit
          $scope.campaign.dailyBudget = $scope.minDailyBudget
        if $scope.minDailyBudget > $scope.campaign.dailyBudget
          $scope.campaign.dailyBudget = $scope.minDailyBudget
        $scope.maxBid = $scope.minDailyBudget

    $scope.increaseMaxBid = ->
      $scope.campaign.maxBid = parseFloat $scope.campaign.maxBid
      if $scope.campaign.totalBudget > $scope.campaign.maxBid
        $scope.campaign.maxBid += 0.1
        $scope.campaign.maxBid = $scope.campaign.maxBid.toFixed 2
        calculateViews()

    $scope.decreaseMaxBid = ->
      $scope.campaign.maxBid = parseFloat $scope.campaign.maxBid
      if $scope.campaign.maxBid > 1
        $scope.campaign.maxBid -= 0.1
        calculateViews()
      $scope.campaign.maxBid = $scope.campaign.maxBid.toFixed 2

    $scope.updateBudget = ->
      $scope.campaign.views = 0 unless $scope.campaign.views?
      $scope.campaign.totalBudget = parseInt($scope.campaign.views * $scope.campaign.maxBid, 10)

    $scope.saveCampaign = ->
      $scope.campaign.dailyBudget = 0 if $scope.unlimitedDailyBudget
      if isEdit
        updateCampaign()
      else
        createCampaign()

    $scope.cancelCampaign = -> $location.path '/campaign/management'

    $scope.changeBugdetLimits()
]
