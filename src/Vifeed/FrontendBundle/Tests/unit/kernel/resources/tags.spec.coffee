describe 'Tags', ->
  beforeEach( -> module 'resources.tags' )

  describe 'Resource', ->
    tags = {}
    httpBackend = {}
    mock = {}
    expect = chai.expect

    beforeEach(inject ($httpBackend, Tags) ->
      httpBackend = $httpBackend
      tags = Tags
      mock = new TagsMock(httpBackend)
      mock.mockOkResponse()
    )

    it 'should have default resource url', ->
      expect(tags.resourceUrl).to.equal('/api/tags')

    it 'should return default options', ->
      options = tags.getOptions()
      expected =
        simple_tags: true,
        tags: [],
        multiple: true,
        maximumInputLength: 16,
        tokenSeparators: [',', ' ']
      expect(options).to.eql(expected)

    it 'should add some value to options', ->
      options = tags.getOptions(custom_value: 'value')
      expected =
        simple_tags: true,
        tags: [],
        multiple: true,
        maximumInputLength: 16,
        tokenSeparators: [',', ' ']
        custom_value: 'value'
      expect(options).to.eql(expected)

    it 'should return tags loaded by "some" word', ->
      reply = []
      tags.loadByWord('some').then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.eql [
        'something', 'someone', 'somebody', 'sometime', 'sometimes'
      ]

    it 'should return tags loaded by "somet" word', ->
      reply = []
      tags.loadByWord('somet').then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.eql ['something', 'sometime', 'sometimes']

    it 'should return tags loaded by "some" and "somet" words', ->
      reply = []
      tags.loadByWord('some').then (response) -> reply = response
      tags.loadByWord('somet').then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.eql [
        'something', 'someone', 'somebody', 'sometime', 'sometimes'
      ]

    it 'should return tags loaded by "so" and "some" words', ->
      reply = []
      tags.loadByWord('so').then (response) -> reply = response
      tags.loadByWord('some').then (response) -> reply = response
      httpBackend.flush()
      expect(reply).to.eql [
        'sony', 'sochi', 'soundcloud', 'south', 'somebody', 'something',
        'someone', 'sometime', 'sometimes'
      ]

    it 'should return empty tags if a word is short', ->
      reply = []
      tags.loadByWord('s').then (response) -> reply = response
      expect(reply).to.eql []

    it 'should return empty tags if no suggestions', ->
      reply = []
      tags.loadByWord('verylongword').then (response) -> reply = response
      expect(reply).to.eql []

