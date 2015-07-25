describe 'TextareaLimiter:', ->
  scope = {}
  compile = {}

  beforeEach module('kernel')

  beforeEach inject(($rootScope, $compile) ->
    scope = $rootScope.$new()
    compile = $compile)

  it 'should create help block', ->
    elm = angular.element '<textarea ' +
        'textarea-limiter ' +
        'ng-model="desc" ' +
        'ng-trim="false" ' +
        'ng-maxlength="10" ' +
        'ng-required="true" ' +
        'rows="5">' +
      '</textarea>'
    compile(elm)(scope)
    scope.$digest()
    helpBlock = elm.next('.help-block')
    expect(helpBlock.text()).toBe 'Осталось 10 символов'
    expect(scope.symbolsLeft).toBe 'Осталось 10 символов'

  it 'should update help block when content is changing', ->
    elm = angular.element '<textarea ' +
        'textarea-limiter ' +
        'ng-model="desc" ' +
        'ng-trim="false" ' +
        'ng-maxlength="10" ' +
        'ng-required="true" ' +
        'rows="5">' +
      '</textarea>'
    compile(elm)(scope)
    scope.$digest()
    helpBlock = elm.next('.help-block')
    scope.desc = 'test'
    elm.scope().$apply()
    expect(scope.symbolsLeft).toBe 'Осталось 6 символов'
    expect(helpBlock.text()).toBe 'Осталось 6 символов'

  it 'should have no more symbols if user input text is too long', ->
    scope.desc = 'this is a very veeeeeeeery looooooong text'
    elm = angular.element '<textarea ' +
        'textarea-limiter ' +
        'ng-model="desc" ' +
        'ng-trim="false" ' +
        'ng-maxlength="45" ' +
        'ng-required="true" ' +
        'rows="5">' +
      '</textarea>'
    compile(elm)(scope)
    scope.$digest()
    helpBlock = elm.next('.help-block')
    elm.scope().$apply()
    expect(scope.symbolsLeft).toBe 'Осталось 3 символа'
    expect(helpBlock.text()).toBe 'Осталось 3 символа'

  it 'should have all symbols when a description is null', ->
    scope.desc = null
    elm = angular.element '<textarea ' +
        'textarea-limiter ' +
        'ng-model="desc" ' +
        'ng-trim="false" ' +
        'ng-maxlength="10" ' +
        'ng-required="true" ' +
        'rows="5">' +
      '</textarea>'
    compile(elm)(scope)
    scope.$digest()
    helpBlock = elm.next('.help-block')
    expect(helpBlock.text()).toBe 'Осталось 10 символов'
    expect(scope.symbolsLeft).toBe 'Осталось 10 символов'
