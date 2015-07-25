class TagsMock
  constructor: (httpBackend) -> @http = httpBackend

  mockOkResponse: ->
    @http
      .when('GET', '/api/tags/so')
      .respond (method, url, data, headers) ->
        [200, ['sony', 'sochi', 'soundcloud', 'south', 'somebody'], {}]
    @http
      .when('GET', '/api/tags/some')
      .respond (method, url, data, headers) ->
        [200, ['something', 'someone', 'somebody', 'sometime', 'sometimes'], {}]
    @http
      .when('GET', '/api/tags/somet')
      .respond (method, url, data, headers) ->
        [200, ['something', 'sometime', 'sometimes'], {}]
    @http
      .when('GET', '/api/tags/verylongword')
      .respond (method, url, data, headers) -> [200, [], {}]
