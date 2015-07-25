describe 'Ages', ->
  beforeEach( -> module 'resources.ages' )

  describe 'Resource', ->
    ages = {}
    httpBackend = {}
    mock = {}
    expect = chai.expect

    beforeEach(inject ($httpBackend, Ages) ->
      httpBackend = $httpBackend
      ages = Ages
      mock = new AgesMock(httpBackend)
      mock.mockOkResponse())

    it 'should have default resource url', ->
      expect(ages.resourceUrl).to.equal '/api/ageranges'

    it 'should return all age ranges', ->
      reply = []
      ages.all().then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.eql [
        {"id":1,"name":"до 13"},
        {"id":2,"name":"14-18"},
        {"id":3,"name":"19-25"},
        {"id":4,"name":"26-35"},
        {"id":5,"name":"36-60"},
        {"id":6,"name":"старше 60"}
      ]
