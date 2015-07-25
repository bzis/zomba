class AgesMock
  constructor: (httpBackend) -> @http = httpBackend

  getAgeRanges: ->
    [{"id":1,"name":"до 13"},
    {"id":2,"name":"14-18"},
    {"id":3,"name":"19-25"},
    {"id":4,"name":"26-35"},
    {"id":5,"name":"36-60"},
    {"id":6,"name":"старше 60"}]

  mockOkResponse: ->
    ages = @getAgeRanges()
    @http
      .when('GET', '/api/ageranges')
      .respond (method, url, data, headers) -> [200, ages, {}]
