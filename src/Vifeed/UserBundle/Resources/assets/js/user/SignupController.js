SignupController = function ($http, $rootScope, $scope, principal, authority, authService, TokenHandler) {
    var apiSingupUrl = '/api/users';

    $scope.user = principal;

    $scope.navType = 'pills';

    $scope.signupData = {
    	email: '',
    	type: 'advertiser'
    };

    // Sign in button handler
    $scope.signupButtonClick = function () {
      // Values to post to authentication service
      var body = {
      	advertiser_registration: {
        	email: $scope.signupData.email 
      }
    };

      // Reset the error messages
      $scope.message = null;

      // POST values to the auth API
      $http
        .put(apiSingupUrl, body)
        .success(function (data) {
          // Tell authService that the user is logged in
          authService.loginConfirmed();
          // Tell the authority that the user is authorized
          authority.authorize(data);

          // Reset credential models
          $scope.username = '';
          $scope.password = '';
        })
        // This error message should only occur if there were no user
        // credentials supplied.
        .error(function () {
          console.log('shit');
          $scope.message = 'Неверный логин или пароль';
          // Broadcast an event stating that sigin failed
          $rootScope.$broadcast('event:signin-failed');
        });
    };

};