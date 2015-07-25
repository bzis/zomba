describe 'Pluralizer', ->
  beforeEach module('i18n.pluralizer')

  describe 'Service', ->
    pluralizer = {}
    expect = chai.expect

    beforeEach(inject ($httpBackend, Pluralizer) ->
      pluralizer = Pluralizer)

    it 'should return a message in Russian for 1 item', ->
      message = pluralizer.ru 1, 'тест', 'теста', 'тестов'
      expect(message).to.equal 'тест'

    it 'should return a message in Russian for 3 items', ->
      message = pluralizer.ru 3, 'тест', 'теста', 'тестов'
      expect(message).to.equal 'теста'

    it 'should return a message in Russian for 5 items', ->
      message = pluralizer.ru 5, 'тест', 'теста', 'тестов'
      expect(message).to.equal 'тестов'

    it 'should return a message in Russian for 21 items', ->
      message = pluralizer.ru 21, 'тест', 'теста', 'тестов'
      expect(message).to.equal 'тест'

    it 'should return a message in Russian for 1321 items', ->
      message = pluralizer.ru 1321, 'тест', 'теста', 'тестов'
      expect(message).to.equal 'тест'
