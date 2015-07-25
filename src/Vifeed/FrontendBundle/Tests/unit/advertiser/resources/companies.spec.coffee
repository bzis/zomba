describe 'Companies', ->
  beforeEach( -> module 'resources.companies' )

  describe 'Resource', ->
    companies = {}
    $httpBackend = {}
    mock = {}
    expect = chai.expect

    beforeEach(inject (_$httpBackend_, Companies) ->
      $httpBackend = _$httpBackend_
      companies = Companies
      mock = new CompaniesMock($httpBackend)
      # mock.mockOkResponse()
    )

    it 'should have default resource url', ->
      expect(companies.resourceUrl).to.equal '/api/users/current/company'

    it 'should return all taxation systems', ->
      systems = [
        { name: 'ОСН' }
        { name: 'УСН' }
      ]
      expect(companies.getTaxationSystems()).to.eql systems

    it 'should return an empty company if new() method called', ->
      company =
        system:
          name: 'ОСН'
        name: ''
        address: ''
        inn: ''
        kpp: ''
        bic: ''
        bankAccount: ''
        correspondentAccount: ''
        contactName: ''
        position: ''
        phone: ''
        isApproved: false
      expect(companies.new()).to.eql company

    it 'should return empty response when no companies', ->
      mock.mockNoCompanyResponse()
      reply = null
      companies.getCurrent().then (response) -> reply = response
      $httpBackend.flush()
      expect(reply).to.eql ''

    it 'should return empty response when getCurrentOrNew() method called and no companies', ->
      company =
        system:
          name: 'ОСН'
        name: ''
        address: ''
        inn: ''
        kpp: ''
        bic: ''
        bankAccount: ''
        correspondentAccount: ''
        contactName: ''
        position: ''
        phone: ''
        isApproved: false
      mock.mockNoCompanyResponse()
      reply = null
      companies.getCurrentOrNew().then (response) -> reply = response
      $httpBackend.flush()
      expect(reply).to.eql company
