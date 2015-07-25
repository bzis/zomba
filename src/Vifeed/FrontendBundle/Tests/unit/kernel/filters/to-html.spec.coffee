describe 'ToHtml filter', ->
  expect = chai.expect

  beforeEach module('toHtml')

  it 'should return empty string when nothing is set', inject ($filter) ->
    toHtml = $filter('toHtml')()
    expect(toHtml).to.be.equal ''

  it 'should return empty string when null is set', inject ($filter) ->
    toHtml = $filter('toHtml')(null)
    expect(toHtml).to.be.equal ''

  it 'should return empty string when undefined is set', inject ($filter) ->
    toHtml = $filter('toHtml')(undefined)
    expect(toHtml).to.be.equal ''

  it 'should return empty string when empty string is set', inject ($filter) ->
    toHtml = $filter('toHtml')('')
    expect(toHtml).to.be.equal ''

  it 'should return empty string when null is set as a string', inject ($filter) ->
    toHtml = $filter('toHtml')('null')
    expect(toHtml).to.be.equal ''

  it 'should return empty string when undefined is set as a string', inject ($filter) ->
    toHtml = $filter('toHtml')('undefined')
    expect(toHtml).to.be.equal ''
