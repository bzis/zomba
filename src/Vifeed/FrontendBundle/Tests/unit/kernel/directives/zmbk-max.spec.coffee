describe 'ZmbkMax:', ->
  scope = {}
  compile = {}
  expect = chai.expect

  beforeEach module('kernel')

  beforeEach(inject ($rootScope, $compile) ->
    scope = $rootScope.$new()
    compile = $compile
  )

  it 'should mark model as invalid if the value too high', ->
    scope.bid = 1000
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-max="100" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'max'
    expect(scope['campaign_form']['bid'].$error.max).to.be.true
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as invalid if the value is float and too high', ->
    scope.bid = 1000.0
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-max="100" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as valid if the value is ok', ->
    scope.bid = 1000
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-max="1000" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$error).to.have.ownProperty 'max'
    expect(scope['campaign_form']['bid'].$error.max).to.be.false
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as valid if the value is float and ok', ->
    scope.bid = 1000.00
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-max="1000" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as invalid if the value is almost ok', ->
    scope.bid = 1000.01
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-max="1000" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as valid if the value is a bit less', ->
    scope.bid = 999.999
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-max="1000" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.true

  it 'should mark model as invalid if the value is higher than other model value', ->
    scope.bid = 1001
    scope.maxBid = 1000
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-max="maxBid" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.false

  it 'should mark model as valid if the value is lower than other model value', ->
    scope.bid = 1500
    scope.maxBid = 1600
    elm = angular.element '<form name="campaign_form" for="campaign">
      <input type="text" name="bid" id="bid" zmbk-max="maxBid" ng-model="bid">
      </form>'
    compile(elm)(scope)
    scope.$digest()
    expect(scope['campaign_form']['bid'].$valid).to.be.true
