class StatisticsMock
  constructor: (httpBackend) -> @http = httpBackend

  mockDataResponseForLastCampaign: ->
    @http
      .when('GET', '/api/campaigns/14/statistics/daily?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) ->
        body = [
          {"views":"2","paid_views":"2","date":"2014-05-03"}
          {"views":"2","paid_views":"1","date":"2014-05-04"}
          {"views":"1","paid_views":"1","date":"2014-05-05"}
          {"views":"6","paid_views":"6","date":"2014-05-06"}
          {"views":"1","paid_views":"1","date":"2014-05-10"}
        ]
        [200, body, {}]
    @http
      .when('GET', '/api/campaigns/14/statistics/geo?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) ->
        body = [
          {"views":"11","name":null,"city_id":null,"latitude":null,"longitude":null}
          {"views":"1","name":"Москва","city_id":23541,"latitude":"55.7522","longitude":"37.6156"}
        ]
        [200, body, {}]
    @http
      .when('GET', '/api/campaigns/14/statistics/geo/countries?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) ->
        body = [
          {"views":"11","name":null,"country_id":null,"percentage":92}
          {"views":"1","name":"Российская Федерация","country_id":20,"percentage":8}
        ]
        [200, body, {}]

  mockDataResponseForFirstCampaign: ->
    @http
      .when('GET', '/api/campaigns/1/statistics/daily?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) ->
        body = [
          {"views":"2","paid_views":"1","date":"2014-05-03"}
          {"views":"2","paid_views":"0","date":"2014-05-04"}
          {"views":"1","paid_views":"1","date":"2014-05-05"}
          {"views":"6","paid_views":"2","date":"2014-05-06"}
          {"views":"1","paid_views":"1","date":"2014-05-10"}
        ]
        [200, body, {}]
    @http
      .when('GET', '/api/campaigns/1/statistics/geo?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) ->
        body = [
          {"views":"11","name":null,"city_id":null,"latitude":null,"longitude":null}
          {"views":"10","name":"Москва","city_id":23541,"latitude":"55.7522","longitude":"37.6156"}
          {"views":"4","name":"Санкт-Петербург","city_id":23540,"latitude":"55.7522","longitude":"37.6156"}
        ]
        [200, body, {}]
    @http
      .when('GET', '/api/campaigns/1/statistics/geo/countries?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) ->
        body = [
          {"views":"900","name":null,"country_id":null,"percentage":90}
          {"views":"50","name":"Российская Федерация","country_id":20,"percentage":5}
          {"views":"50","name":"Германия","country_id":1,"percentage":5}
        ]
        [200, body, {}]

  mockNoDataResponseForFirstCampaign: ->
    @http
      .when('GET', '/api/campaigns/1/statistics/daily?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/1/statistics/geo?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/1/statistics/geo/countries?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) -> [200, [], {}]

  mockOkResponse: ->
    @http
      .when('GET', '/api/campaigns/1/statistics/daily?date_from=2014-01-01&date_to=2014-01-10')
      .respond (method, url, data, headers) -> [200, [{}, {}], {}]
    @http
      .when('GET', '/api/campaigns/999/statistics/daily?date_from=2014-01-01&date_to=2014-01-10')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/999/statistics/hourly/today')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/999/statistics/hourly/yesterday')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/999/statistics/geo?date_from=2014-01-01&date_to=2014-01-10')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/999/statistics/geo/countries?date_from=2014-01-01&date_to=2014-01-10')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/999/statistics/geo/countries/999?date_from=2014-01-01&date_to=2014-01-10')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/3/statistics/daily?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/3/statistics/geo?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) -> [200, [], {}]
    @http
      .when('GET', '/api/campaigns/3/statistics/geo/countries?date_from=2014-05-03&date_to=2014-05-10')
      .respond (method, url, data, headers) -> [200, [], {}]
