describe 'VK', ->
  beforeEach( -> module 'resources.vk' )

  describe 'Resource', ->
    vk = {}
    $rootScope = {}
    expect = chai.expect

    beforeEach(inject (_$rootScope_, $window, Vk) ->
      $rootScope = _$rootScope_
      $window.VK = (new VkMock()).create()
      vk = Vk
    )

    it 'should have correct default flags value', ->
      expect(vk.flags).to.equal 1319424

    it 'should return confirmation is correct for zmbk#1', ->
      confirmation = null
      vk.confirmGroupAccess('http://vk.com/public11113333').then (response) ->
        confirmation = response
      $rootScope.$digest()
      expect(confirmation).to.have.ownProperty 'confirmed'
      expect(confirmation).to.have.ownProperty 'vk_id'
      expect(confirmation.confirmed).to.be.true
      expect(confirmation.vk_id).to.be.equal 11113333

    it 'should return confirmation is correct for zmbk#2', ->
      confirmation = null
      vk.confirmGroupAccess('http://vk.com/the_zmbk').then (response) ->
        confirmation = response
      $rootScope.$digest()
      expect(confirmation).to.have.ownProperty 'confirmed'
      expect(confirmation).to.have.ownProperty 'vk_id'
      expect(confirmation.confirmed).to.be.true
      expect(confirmation.vk_id).to.be.equal 11112222

    it 'should return confirmation is correct for zmbk#3', ->
      confirmation = null
      vk.confirmGroupAccess('http://vk.com/event11114444').then (response) ->
        confirmation = response
      $rootScope.$digest()
      expect(confirmation).to.have.ownProperty 'confirmed'
      expect(confirmation).to.have.ownProperty 'vk_id'
      expect(confirmation.confirmed).to.be.true
      expect(confirmation.vk_id).to.be.equal 11114444

    it 'should return confirmation is correct for zmbk#4', ->
      confirmation = null
      vk.confirmGroupAccess('http://vk.com/club11115555').then (response) ->
        confirmation = response
      $rootScope.$digest()
      expect(confirmation).to.have.ownProperty 'confirmed'
      expect(confirmation).to.have.ownProperty 'vk_id'
      expect(confirmation.confirmed).to.be.true
      expect(confirmation.vk_id).to.be.equal 11115555

    it 'should return confirmation is not correct', ->
      confirmation = null
      vk.confirmGroupAccess('http://vk.com/something_wrong').catch (response) ->
        confirmation = response
      $rootScope.$digest()
      expect(confirmation).to.have.ownProperty 'confirmed'
      expect(confirmation).to.have.ownProperty 'message'
      expect(confirmation.confirmed).to.be.false
      expect(confirmation.message).to.contain 'Убедитесь, что вы являетесь администратором'
