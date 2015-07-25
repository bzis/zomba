describe 'ZmbkMin:', ->
  scope = {}
  compile = {}
  expect = chai.expect

  beforeEach module('kernel')

  beforeEach(inject ($rootScope, $compile) ->
    scope = $rootScope.$new()
    compile = $compile
  )

  it 'should mark model as invalid if the value too small', ->
    scope.bid = 0
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-min="1" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'min'
    expect(scope['campaign_form']['bid'].$error.min).to.be.true
    expect(scope['campaign_form']['bid'].$valid).to.be.false
    # expect(.$valid).to.be.false

  it 'should mark model as invalid if the value is float and too small', ->
    scope.bid = 0.0
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-min="1" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as valid if the value is ok', ->
    scope.bid = 1
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-min="1" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'min'
    expect(scope['campaign_form']['bid'].$error.min).to.be.false
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as valid if the value is float and ok', ->
    scope.bid = 1.0
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-min="1" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as invalid if the value is almost ok', ->
    scope.bid = 0.999
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-min="1" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as valid if the value is a bit more', ->
    scope.bid = 1.001111
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-min="1" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as invalid if the value is lower than other model value', ->
    scope.bid = 0.5
    scope.minBid = 1
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-min="minBid" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as valid if the value is higher than other model value', ->
    scope.bid = 1.5
    scope.minBid = 1
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-min="minBid" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.true
