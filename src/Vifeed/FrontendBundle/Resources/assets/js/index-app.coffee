angular.module('indexApp', [
  'cookiesModule'
  'security'
  'kernel'
  'angulartics'
  'angulartics.google.analytics'
]).run [
  '$rootScope', '$window', 'security', 'APP.CONFIG', 'ProgressBar', '$analytics',
  ($rootScope, $window, security, config, ProgressBar, $analytics) ->
    'use strict'

    isGaUidSet = false

    $rootScope.$on '$routeChangeStart', (event, current, previous) ->
      ProgressBar.start() if current.$$route?.resolve

    $rootScope.$on '$routeChangeSuccess', ->
      if security.isAuthenticated()
        security.fetchCurrentUser().then (user) ->
          unless isGaUidSet
            $analytics.setUserProperties { uid: user.id }
            isGaUidSet = true
      ProgressBar.stop()
      $window.scrollTo 0, 0

    $rootScope.$on '$routeChangeError', ->
      ProgressBar.stop()

    # Shows the login form if a user is not logged in but
    # tries to reach private part
    # We should use the global location object
    hash = $window.location.hash.replace /^#\//, ''
    return if hash is ''
    if not security.isAuthenticated()
      showLoginForm = true
      for route in config.publicRoutes
        if route.slice(0, hash.length) is hash
          showLoginForm = false
          break
      security.showLogin() if showLoginForm
]
