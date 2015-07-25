class CompaniesMock
  constructor: (httpBackend) -> @http = httpBackend

  # getCampaignList: -> [
    # {"id":1,"hash_id":"6BXZnR","name":"The Many Rivers Ensemble - Upwelling 2013 ( HD )","hash":"QmmE5e0T1IM","description":"Brand new Hand Massive website is live now!\nHANG MASSIVE WEBSITE : http://hangmusic.com\nBUY SINGLE ON iTUNES HERE: http://tinyurl.com/ctw5pxx\n\nFree downloads, videos, media, bookings and info\n\nThis is the first single release from an album to be released","gender":null,"general_budget":1000,"daily_budget":80,"general_budget_used":0,"daily_budget_used":0,"total_views":0,"paid_views":0,"bid":1,"duration":0,"status":"paused","social_data":null,"countries":[],"tags":[],"age_ranges":[]},
    # {"id":3,"hash_id":"3na1n6","name":"Видео из This is Хорошо. Сова. Что это у тебя там?","hash":"TqnA7vhsD4o","description":null,"gender":null,"general_budget":300000,"daily_budget":0,"general_budget_used":194055,"daily_budget_used":194055,"total_views":15841,"paid_views":12937,"bid":8,"duration":0,"status":"on","social_data":null,"countries":[{"id":127,"name":"Австрия"}],"tags":["youtube","fun"],"age_ranges":[]},
    # {"id":14,"hash_id":"RrOQK5","name":"LIMBO Game iPhone iPad iPod Touch Review (HD)","hash":"mWcqvY35hcw","description":"Limbo rules! Play now, hands down!","gender":"male","general_budget":200000,"daily_budget":100,"general_budget_used":68866,"daily_budget_used":68866,"total_views":11973,"paid_views":9838,"bid":7,"duration":0,"status":"on","social_data":null,"countries":[{"id":1,"name":"Австралия"},{"id":21,"name":"Великобритания"},{"id":23,"name":"США"},{"id":29,"name":"Германия"},{"id":59,"name":"Швейцария"},{"id":127,"name":"Австрия"}],"tags":["game","mobile","puzzle"],"age_ranges":[{"id":2,"name":"14-18"},{"id":3,"name":"19-25"},{"id":4,"name":"26-35"}]}
  # ]

  mockNoCompanyResponse: ->
    @http
      .when('GET', '/api/users/current/company')
      .respond (method, url, data, headers) -> [200, '', {}]

  # mockOkResponse: ->
    # mockCampaigns = @getCampaignList()
    # @http
    #   .when('GET', '/api/users/current/company')
    #   .respond (method, url, data, headers) ->
    #     [200, {}, {}]
    # @http
    #   .when('PUT', '/api/campaigns/1')
    #   .respond (method, url, data, headers) ->
    #     [200, mockCampaigns[0], {}]
    # @http
    #   .when('GET', '/api/campaigns/3')
    #   .respond (method, url, data, headers) ->
    #     [200, mockCampaigns[1], {}]
    # @http
    #   .when('GET', '/api/campaigns')
    #   .respond (method, url, data, headers) ->
    #     [200, mockCampaigns, {}]
