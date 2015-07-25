angular.module('profile').controller 'ProfileCtrl', [
  '$scope', '$location', 'security', 'Companies', 'ProgressBar', 'Utility', 'company',
  ($scope, $location, security, Companies, ProgressBar, Utility, company) ->
    'use strict'

    return unless security.isAuthenticated()

    $scope.hasCompany = false
    $scope.loadingProfile = false
    $scope.loadingPasswords = false
    $scope.loadingCompany = false
    $scope.errorList = []
    $scope.passwordErrorList = []
    $scope.campaignErrorList = []
    $scope.profile = security.currentUser
    $scope.taxationSystems = Companies.getTaxationSystems()
    $scope.company = company

    resetPasswordModels = ->
      $scope.password =
        current: null
        newOne: null
        newOneRepeated: null

    $scope.updateUserInfo = ->
      ProgressBar.start()
      $scope.loadingProfile = true
      $scope.errorList = []
      profile =
        first_name: $scope.profile.firstName
        surname: $scope.profile.lastName
        email: $scope.profile.email
        phone: "+7#{$scope.profile.phone}"
        notification:
          email: $scope.profile.notification.email
          sms: $scope.profile.notification.sms
          news: $scope.profile.notification.news
      security.updateUser(profile).catch (response) ->
        $scope.errorList = Utility.toErrorList response.data.errors
      .finally( ->
        $scope.loadingProfile = false
        ProgressBar.stop()
      )

    $scope.updateUserPasswords = ->
      ProgressBar.start()
      $scope.loadingPasswords = true
      $scope.passwordErrorList = []
      passwords =
        currentPassword: $scope.password.current
        plainPassword:
          first: $scope.password.newOne
          second: $scope.password.newOneRepeated
      security.updateUserPasswords(passwords).then(
        ( -> resetPasswordModels()),
        (response) -> $scope.passwordErrorList = Utility.toErrorList response.data.errors
      ).finally( ->
        $scope.loadingPasswords = false
        ProgressBar.stop()
      )

    $scope.updateCompany = ->
      ProgressBar.start()
      $scope.loadingCompany = true
      $scope.companyErrorList = []
      Companies.updateCompany($scope.company).catch (response) ->
        $scope.companyErrorList = Utility.toErrorList response.data.errors
      .finally( ->
        $scope.loadingCompany = false
        ProgressBar.stop()
      )
]
