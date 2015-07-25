# TODO: Added tests for float and string input values
describe 'ZmbkRange:', ->
  scope = {}
  compile = {}
  expect = chai.expect

  beforeEach module('kernel')

  beforeEach(inject ($rootScope, $compile) ->
    scope = $rootScope.$new()
    compile = $compile
  )

  it 'should mark model as invalid if bid is small', ->
    scope.bid = 0
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" ng-model="bid" zmbk-range min="1" max="100">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'range'
    expect(scope['campaign_form']['bid'].$error.range).to.be.true
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as invalid if bid is too high', ->
    scope.bid = 101
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" ng-model="bid" zmbk-range min="1" max="100">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'range'
    expect(scope['campaign_form']['bid'].$error.range).to.be.true
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as valid if bid is in a range', ->
    scope.bid = 50
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" ng-model="bid" zmbk-range min="1" max="100">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'range'
    expect(scope['campaign_form']['bid'].$error.range).to.be.false
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as valid if bid is equal to lower border', ->
    scope.bid = 1
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" ng-model="bid" zmbk-range min="1" max="100">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'range'
    expect(scope['campaign_form']['bid'].$error.range).to.be.false
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as valid if bid is equal to higher border', ->
    scope.bid = 100
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" ng-model="bid" zmbk-range min="1" max="100">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'range'
    expect(scope['campaign_form']['bid'].$error.range).to.be.false
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as valid if bid is zero and there is no lower border', ->
    scope.bid = 0
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" ng-model="bid" zmbk-range max="100">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'range'
    expect(scope['campaign_form']['bid'].$error.range).to.be.false
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as valid if bid is 1000 and there is no higher border', ->
    scope.bid = 1000
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" ng-model="bid" zmbk-range min="100">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'range'
    expect(scope['campaign_form']['bid'].$error.range).to.be.false
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as invalid if bid is lower then zero and there are no borders', ->
    scope.bid = -1
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" ng-model="bid" zmbk-range>
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'range'
    expect(scope['campaign_form']['bid'].$error.range).to.be.true
    expect(scope['campaign_form']['bid'].$valid).to.be.false
