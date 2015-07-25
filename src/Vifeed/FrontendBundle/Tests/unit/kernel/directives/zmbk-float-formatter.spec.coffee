describe 'ZmbkFloatFormatter:', ->
  scope = {}
  compile = {}
  expect = chai.expect

  beforeEach module('kernel')

  beforeEach(inject ($rootScope, $compile) ->
    scope = $rootScope.$new()
    compile = $compile
  )

  it 'should convert comma to dot', ->
    elm = angular.element '<input type="text" name="bid" id="bid" ng-model="bid" zmbk-float-formatter>'
    htmlElement = compile(elm)(scope)
    scope.$digest()
    htmlElement.val '1,10'
    htmlElement.trigger 'input'
    scope.$digest()
    expect(scope.bid).to.be.equal '1.10'

  it 'should convert dot to dot', ->
    elm = angular.element '<input type="text" name="bid" id="bid" ng-model="bid" zmbk-float-formatter>'
    htmlElement = compile(elm)(scope)
    scope.$digest()
    htmlElement.val '1.10'
    htmlElement.trigger 'input'
    scope.$digest()
    expect(scope.bid).to.be.equal '1.10'

  it 'should return zero if empty value has been set', ->
    scope.bid = 10.10
    elm = angular.element '<input type="text" name="bid" id="bid" ng-model="bid" zmbk-float-formatter>'
    htmlElement = compile(elm)(scope)
    scope.$digest()
    htmlElement.val ''
    htmlElement.trigger 'input'
    scope.$digest()
    expect(scope.bid).to.be.equal 0

  it 'should return zero if null has been set', ->
    scope.bid = 10.10
    elm = angular.element '<input type="text" name="bid" id="bid" ng-model="bid" zmbk-float-formatter>'
    htmlElement = compile(elm)(scope)
    scope.$digest()
    htmlElement.val null
    htmlElement.trigger 'input'
    scope.$digest()
    expect(scope.bid).to.be.equal 0

  it 'should return zero if string has been set', ->
    scope.bid = 10.10
    elm = angular.element '<input type="text" name="bid" id="bid" ng-model="bid" zmbk-float-formatter>'
    htmlElement = compile(elm)(scope)
    scope.$digest()
    htmlElement.val 'test'
    htmlElement.trigger 'input'
    scope.$digest()
    expect(scope.bid).to.be.equal 0
