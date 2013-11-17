SignupController = function (security, $http, $rootScope, $scope) {
    var apiSingupUrl = '/api/users';


    $scope.navType = 'pills';

    $scope.signupData = {
    	email: '',
    	type: 'advertiser'
    };

    $scope.userTypeAdvertiser = function() {
      $scope.signupData.type = 'advertiser';
    };

    $scope.userTypePublisher = function() {
      $scope.signupData.type = 'publisher';
    };

    // Sign in button handler
    $scope.signupButtonClick = function () {
      // Values to post to authentication service
      var body = {};
      if ($scope.signupData.type == 'advertiser') {
        body = {
        	advertiser_registration: {
          	email: $scope.signupData.email 
          }
        };
      } else {
        body = {
          publisher_registration: {
            email: $scope.signupData.email
          }
        };
      }

      // Reset the error messages
      $scope.message = null;

      // POST values to the auth API
      $http
        .put(apiSingupUrl, body)
        .success(function (data) {
          security.setUser($scope.signupData.email, data.token, $scope.signupData.type);
          window.location = '/';
        })
        // This error message should only occur if there were no user
        // credentials supplied.
        .error(function () {
          $scope.message = 'Неверный логин или пароль';
          // Broadcast an event stating that sigin failed
          $rootScope.$broadcast('event:signin-failed');
        });
    };

};