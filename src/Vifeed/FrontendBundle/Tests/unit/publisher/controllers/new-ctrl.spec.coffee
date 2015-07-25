describe 'NewCtrl:', ->
  beforeEach( -> module 'publisherApp' )

  describe 'Controller', ->
    scope = {}

    beforeEach(inject ($rootScope, $controller, security) ->
      scope = $rootScope.$new()
      security.currentUser.email = 'publisher@mail.com'
      security.currentUser.token = 'ThisIsAToken'
      security.currentUser.type = 'publisher'
      $controller 'NewCtrl', $scope: scope, countries: [], security: security
    )

    it 'should have a vk platform if a user specified "vk.com"', ->
      scope.platform =
        url: 'http://vk.com',
        type: 'site'
      scope.detectType()
      scope.$digest()
      expect(scope.platform.type).toBe 'vk'

    it 'should have a vk platform if a user specified "vkontakte.ru"', ->
      scope.platform =
        url: 'http://vkontakte.ru',
        type: 'site'
      scope.detectType()
      scope.$digest()
      expect(scope.platform.type).toBe 'vk'

    it 'should have a vk platform if a user specified "www.vkontakte.ru"', ->
      scope.platform =
        url: 'http://www.vkontakte.ru',
        type: 'site'
      scope.detectType()
      scope.$digest()
      expect(scope.platform.type).toBe 'vk'

    it 'should have a vk platform if a user specified "www.vk.com"', ->
      scope.platform =
        url: 'http://www.vk.com',
        type: 'site'
      scope.detectType()
      scope.$digest()
      expect(scope.platform.type).toBe 'vk'

    it 'should have a site platform if a user specified "vkontakte.com"', ->
      scope.platform =
        url: 'http://vkontakte.com',
        type: 'site'
      scope.detectType()
      scope.$digest()
      expect(scope.platform.type).toBe 'vk'

    it 'should have a site platform if a user specified "www.vkontakte.com"', ->
      scope.platform =
        url: 'http://www.vkontakte.com',
        type: 'site'
      scope.detectType()
      scope.$digest()
      expect(scope.platform.type).toBe 'vk'

    it 'should have a site platform if a user specified "vk.ru"', ->
      scope.platform =
        url: 'http://vk.ru',
        type: 'site'
      scope.detectType()
      scope.$digest()
      expect(scope.platform.type).toBe 'site'

    it 'should have a site platform if a user specified "google.com"', ->
      scope.platform =
        url: 'http://google.com',
        type: 'site'
      scope.detectType()
      scope.$digest()
      expect(scope.platform.type).toBe 'site'
