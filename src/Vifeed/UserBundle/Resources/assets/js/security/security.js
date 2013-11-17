// Based loosely around work by Witold Szczerba - https://github.com/witoldsz/angular-http-auth
angular.module('security.service', [
  'security.retryQueue',    // Keeps track of failed requests that need to be retried once the user logs in
  'security.login',         // Contains the login form template and controller
  'ui.bootstrap.modal',     // Used to display the login form as a modal dialog.
  'templates-angularUiBootstrapModal',
  'security.wsse'
])

.factory('security', ['TokenHandler', 'Cookies', '$http', '$q', '$location', 'securityRetryQueue', '$modal', function(tokenHandler, Cookies, $http, $q, $location, queue, $modal, $log) {

  // Redirect to the given url (defaults to '/')
  function redirect(url) {
    url = url || '/';
    $location.path(url);
  }

  // Login form dialog stuff
  var loginDialog = null;
  function openLoginDialog() {
    if ( !loginDialog ) {
      loginDialog = $modal.open({
        templateUrl: '/bundles/vifeeduser/partials/security/form.tpl.html',
        controller: 'LoginFormController'
      });
      loginDialog.result.finally(onLoginDialogClose);
    }
  }
  function closeLoginDialog(success) {
    if (loginDialog) {
      loginDialog.close(success);
    }
  }
  function onLoginDialogClose(success) {
    loginDialog = null;
    if ( success ) {
      queue.retryAll();
    } else {
      queue.cancelAll();
      redirect();
    }
  }

  // Register a handler for when an item is added to the retry queue
  queue.onItemAddedCallbacks.push(function(retryItem) {
    if ( queue.hasMore() ) {
      service.showLogin();
    }
  });

  // The public API of the service
  var service = {

    // Get the first reason for needing a login
    getLoginReason: function() {
      return queue.retryReason();
    },

    // Show the modal login dialog
    showLogin: function() {
      openLoginDialog();
    },

    signup: function() {
      window.location = Routing.generate('sign_up');
    },

    // Attempt to authenticate a user by the given email and password
    login: function(email, password) {
      var request = $http.post(Routing.generate('api_fos_user_security_check'), {_username: email, _password: password});

      return request.then(function(response) {
        if (response.data.success) {
          this.setUser(email, response.data.token, response.data.type);
        }
        
        if ( service.isAuthenticated() ) {
          closeLoginDialog(true);
        }
        return service.isAuthenticated();
      }.bind(this));
    },

    // Give up trying to login and clear the retry queue
    cancelLogin: function() {
      closeLoginDialog(false);
      redirect();
    },

    // Logout the current user and redirect
    logout: function(redirectTo) {
      $http.post('/logout').then(function() {
        service.currentUser = null;
        Cookies.removeItem('user_token', '/');
        Cookies.removeItem('user_email', '/');
        Cookies.removeItem('user_type', '/');
        redirect(redirectTo);
      });
    },

    // Ask the backend to see if a user is already authenticated - this may be from a previous session.
    requestCurrentUser: function() {
      if ( service.isAuthenticated() ) {
        return $q.when(service.currentUser);
      } else {
        return $http({
          url: '/api/user',
          method: 'GET',
          headers: {
            'X-WSSE': tokenHandler.getCredentials(service.currentUser.email, service.currentUser.token)
          }
        }).then(function(response) {
          service.currentUser = response.data.user;
          return service.currentUser;
        });
      }
    },

    setUser: function(email, token, type) {
      this.currentUser = {
        email: email,
        token: token,
        type: type
      };
      Cookies.setItem('user_email', this.currentUser.email, 7200, '/');
      Cookies.setItem('user_token', this.currentUser.token, 7200, '/');
      Cookies.setItem('user_type', this.currentUser.type, 7200, '/');

    },

    currentUser: null,


    // Is the current user authenticated?
    isAuthenticated: function(){
      return !!service.currentUser;
    },
    
    // Is the current user an adminstrator?
    isAdmin: function() {
      return !!(service.currentUser && service.currentUser.admin);
    }
  };

    // Information about the current user
    if(Cookies.hasItem('user_token') && Cookies.hasItem('user_email') && Cookies.hasItem('user_type')){
      service.currentUser = {
        email: Cookies.hasItem('user_email'),
        token: Cookies.hasItem('user_token'),
        type: Cookies.hasItem('user_type')
      };
    }

  return service;
}]);
