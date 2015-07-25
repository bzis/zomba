// var mockModule = require('./../../mock/main-mock');

describe('a user', function () {
  'use strict';

  // beforeEach(function () {
  //   // $httpBackend = injector.get('$httpBackend');
  //
  // });

  it('should see the auth form when clicks on the sign in link', function () {
    browser.get('/');
    element(by.id('signin-link')).click();

    expect(element(by.name('form')).isPresent()).toBe(true);
    expect(element(by.name('email')).isPresent()).toBe(true);
    expect(element(by.name('password')).isPresent()).toBe(true);
  });

  it('should see an error if he set wrong data', function () {
    // mockModule.httpBackendMock();
    console.log(window.angular);
  //   browser.get('/');
  //   element(by.id('signin-link')).click();

    // expect(element(by.name('form')).isPresent()).toBe(true);
    // expect(element(by.name('email')).isPresent()).toBe(true);
    // expect(element(by.name('password')).isPresent()).toBe(true);
  });

  // TODO: realization is required
  // it('is signed out', function () {
  // });
});

/*
describe('Controller: LoginCtrl', function() {

  var LoginCtrl, injector, $rootScope, $scope, $parentScope, $controller, $cookies, $timeout, $httpBackend, ParentCtrl;
  var mockValues = {};

  beforeEach(inject(function($injector) {
    injector    = $injector;
    $rootScope  = injector.get('$rootScope');
    $controller = injector.get('$controller');
    $cookies    = injector.get('$cookies');
    $timeout    = injector.get('$timeout');

    $parentScope = $rootScope.$new();
    ParentCtrl = $controller('ParentCtrl', {
      $scope : $parentScope
    });
    $httpBackend = injector.get('$httpBackend');

    $scope  = $rootScope.$new();
    LoginCtrl = $controller('LoginCtrl', {
      $scope: $scope
    });

    $httpBackend.whenPOST(/sessions\/new/, {UserId: 'payjoe', Password: 'password'})
      .respond({
        authToken: 'xxxxxxxxxx',
        userData: {
          UserId: 'payjoe'
          // Other data
        }
      })
  }));

  it('login with incorrect credentials should reset userId &amp; password', function() {
    $scope.userId = 'payjoe';
    $scope.userPassword = 'wrong-password';
    $scope.signinClick();
    $httpBackend.flush();
    $timeout(function() {
      expect($scope.userId).toEqual('');
      expect($scope.userPassword).toEqual('');
    }, 2000);
  });

  describe('login with correct credentials', function() {
    beforeEach(function() {
      $scope.userId = 'payjoe';
      $scope.userPassword = 'password';
    });

    it('should login correctly', function() {
      $scope.signinClick();
      $httpBackend.flush();
      expect($rootScope.loggedIn).toBeTruthy();
      expect($cookies.loggedInUser).toBeUndefined();
    });

    it('and remember me should have cookies with userId', function() {
      $scope.rememberMe = true;
      $scope.signinClick();
      $httpBackend.flush();
      expect($cookies.loggedInUser).toEqual($scope.userId);
    })
  });
});
*/
