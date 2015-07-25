class AuthMock
  constructor: (httpBackend) -> @http = httpBackend

  configureFailLogin: ->
    @http
      .whenPOST('/api/users/login_check')
      .respond (method, url, data, headers) ->
        [401, message: 'Bad credentials', {}]
