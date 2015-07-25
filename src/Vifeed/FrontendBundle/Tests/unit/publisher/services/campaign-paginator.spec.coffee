# TODO:: create tests
# describe 'CampaignPaginator', ->
#   beforeEach( -> module 'publisherApp' )

#   describe 'Service', ->
#     $httpBackend = {}
#     paginator = {}
#     expect = chai.expect

#     beforeEach(inject (_$httpBackend_, CampaignPaginator) ->
#       $httpBackend = _$httpBackend_
#       paginator = CampaignPaginator
#       mock = new CampaignsMock($httpBackend)
#       mock.mockOkResponse()
#     )

#     it 'should contain correct settings', ->
#       paginator.setup {
#         platformId: 1
#         countryId: 10
#         page: 1
#         perPage: 100
#       }
#       expect(paginator).to.have.ownProperty 'platformId'
#       expect(paginator).to.have.ownProperty 'countryId'
#       expect(paginator).to.have.ownProperty 'page'
#       expect(paginator).to.have.ownProperty 'perPage'
#       expect(paginator.platformId).to.be.eql 1
#       expect(paginator.countryId).to.be.eql 10
#       expect(paginator.page).to.be.eql 1
#       expect(paginator.perPage).to.be.eql 100

#     it 'should contain default settings', ->
#       paginator.setup { platformId: 1 }
#       expect(paginator).to.have.ownProperty 'platformId'
#       expect(paginator).to.have.ownProperty 'countryId'
#       expect(paginator).to.have.ownProperty 'page'
#       expect(paginator).to.have.ownProperty 'perPage'
#       expect(paginator.platformId).to.be.eql 1
#       expect(paginator.countryId).to.be.eql null
#       expect(paginator.page).to.be.eql 1
#       expect(paginator.perPage).to.be.eql 10

#     it 'should throw error if platformId is not set', ->
#       fn = -> paginator.load()
#       expect(fn).to.throw /Platform id is not set/

#     it 'should return campaigns and paginator on success', ->
#       reply = null
#       paginator.setup({ platformId: 1, perPage: 1 }).load()
#     #   # .then (response) -> reply = response
#       $httpBackend.flush()
#       expect(reply).to.have.ownProperty 'campaigns'
#       expect(reply).to.have.ownProperty 'paginator'
