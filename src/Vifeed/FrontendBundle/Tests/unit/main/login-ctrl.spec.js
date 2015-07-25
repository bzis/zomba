// describe('LoginFormController:', function () {
//   beforeEach(function () {
//     module('indexApp');
//   });

//   describe("Controller", function () {
//     var scope, httpBackend;

//     beforeEach(inject(function ($rootScope, $controller, $httpBackend) {
//       scope = $rootScope.$new();
//       httpBackend = $httpBackend;
//       $controller('LoginFormController', { $scope: scope });
//     }));

//     it("should have auth error when incorrect user data has been set", function () {
//       var auth = new AuthMock(httpBackend);
//       auth.configureFailLogin();

//       scope.user = {
//         email: 'test@mail.com',
//         password: 'some_password',
//         rememberMe: 1
//       };

//       scope.login();
//       httpBackend.flush();

//       expect(scope.authError).toBe('Неверный логин или пароль.');
//     });
//   });
// });
