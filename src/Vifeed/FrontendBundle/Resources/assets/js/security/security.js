// Based loosely around work by Witold Szczerba - https://github.com/witoldsz/angular-http-auth
angular.module('security.service', [
  'security.retryQueue',    // Keeps track of failed requests that need to be retried once the user logs in
  'security.login',         // Contains the login form template and controller
  'ui.bootstrap.modal',     // Used to display the login form as a modal dialog.
  'templates-angularUiModal',
  'templates-security',
  'security.wsse'
])
.factory('security',
  ['TokenHandler', 'Cookies', '$http', '$q', '$window', '$location', 'securityRetryQueue', '$modal',
  function (tokenHandler, Cookies, $http, $q, $window, $location, queue, $modal) {
  // Redirect to the given url (defaults to '/')
  function redirect(url) {
    url = url || '/';
    $location.path(url);
  }

  // Strict redirect using window.location to the given url (defaults to '/')
  function strictRedirect(url) {
    url = url || '/';
    $window.location = url;
  }

  // Login form dialog stuff
  var loginDialog = null;

  function openLoginDialog() {
    if (!loginDialog) {
      loginDialog = $modal.open({
        templateUrl: '/bundles/vifeedfrontend/partials/security/form.tpl.html',
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

    if (success) {
      queue.retryAll();
    } else {
      queue.cancelAll();
      strictRedirect();
    }
  }

  function getWsseHeader(currentUser) {
    var wsseHeader = { 'X-WSSE': null };

    if (angular.isObject(currentUser)) {
      wsseHeader = {
        'X-WSSE': tokenHandler.getCredentials(currentUser.email, currentUser.token)
      };
    }

    return wsseHeader;
  }

  // Register a handler for when an item is added to the retry queue
  queue.onItemAddedCallbacks.push(function (retryItem) {
    if (queue.hasMore()) {
      service.showLogin();
    }
  });

  // The public API of the service
  var service = {
    currentUser: null,

    initialize: function () {
      this.currentUser = {
        id: 0,
        email: '',
        token: '',
        type: '',
        admin: false,
        balance: 0,
        firstName: '',
        lastName: '',
        fullName: '',
        phone: '',
        notification: {
          email: 0,
          sms: 0,
          news: 0
        }
      };

      // Information about the current user
      if (Cookies.hasItem('user_token') && Cookies.hasItem('user_email') && Cookies.hasItem('user_type')) {
        this.currentUser.email = Cookies.getItem('user_email');
        this.currentUser.token = Cookies.getItem('user_token');
        this.currentUser.type = Cookies.getItem('user_type');
      }
    },

    // Get the first reason for needing a login
    getLoginReason: function () {
      return queue.retryReason();
    },

    // Show the modal login dialog
    showLogin: function () {
      openLoginDialog();
    },

    // Redirects to the signup page
    goToSignUpPage: function () {
      window.location = Routing.generate('sign_up_page');
    },

    signup: function (user) {
      return $http.put(
        Routing.generate('sign_up'),
        user
      ).then(function (response) {
        service.currentUser.email = user.registration.email;
        service.currentUser.token = response.data.token;
        service.currentUser.type = user.registration.type;
        service.storeUserToCookie();
      });
    },

    // Attempt to authenticate a user by the given email and password
    login: function (email, password, isRemember) {
      var request = $http.post(
        Routing.generate('sign_in'), {
          _username: email,
          _password: password,
          _remember_me: isRemember
        }
      );

      return request.then(function (response) {
        this.currentUser.email = email;
        this.currentUser.token = response.data.token;
        this.currentUser.type = response.data.type;
        service.storeUserToCookie();
        closeLoginDialog(true);

        return service.isAuthenticated();
      }.bind(this));
    },

    // Give up trying to login and clear the retry queue
    cancelLogin: function () {
      closeLoginDialog(false);
      redirect();
    },

    // Logout the current user
    logout: function () {
      return $http.delete(
        Routing.generate('sign_out'),
        { headers: getWsseHeader(service.currentUser) }
      ).then(function (response) {
        if (response.status === 204) {
          service.currentUser = null;
          Cookies.removeItem('user_token', '/');
          Cookies.removeItem('user_email', '/');
          Cookies.removeItem('user_type', '/');
        }
      });
    },

    sendPasswordLink: function (email) {
      return $http.post('/api/users/reset', { email: email });
    },

    updatePasswordByToken: function (token, passwords) {
      var data = {
        token: token,
        resetting: {
          plainPassword: passwords
        }
      };

      return $http.post('/api/users/reset', data);
    },

    // Ask the backend to see if a user is already authenticated - this may be from a previous session.
    requestCurrentUser: function () {
      if (service.isAuthenticated()) {
        return $q.when(service.currentUser);
      } else {
        return service.fetchCurrentUser();
      }
    },

    fetchCurrentUser: function () {
      return $http.get(
        // todo: Move this route to an external routing config file
        '/api/users/current',
        { headers: getWsseHeader(service.currentUser) }
      ).then(function (response) {
        var user = response.data;

        service.currentUser.id = user.id;
        service.currentUser.email = user.email;
        service.currentUser.type = user.type;
        service.currentUser.balance = user.balance;
        service.currentUser.firstName = user.first_name;
        service.currentUser.lastName = user.surname;
        service.currentUser.fullName = [user.first_name, user.surname].join(' ').trim();

        if (user.phone !== null && user.phone !== undefined) {
          service.currentUser.phone = user.phone.replace(/^\+7/, '');
        }

        service.currentUser.notification.email = user.notification.email || 0;
        service.currentUser.notification.sms = user.notification.sms || 0;
        service.currentUser.notification.news = user.notification.news || 0;

        return service.currentUser;
      });
    },

    fetchUserCompany: function () {
      return $http.get(
        '/api/users/current/company',
        { headers: getWsseHeader(service.currentUser) }
      );
    },

    updateUser: function (profile) {
      return $http({
        method: 'PATCH',
        // todo: Move this route to an external routing config file
        url: '/api/users/current',
        data: { profile: profile },
        headers: getWsseHeader(service.currentUser)
      }).then(function () {
        service.currentUser.email = profile.email;
        // service.currentUser.firstName = profile.first_name;
        // service.currentUser.lastName = profile.surname;
        service.currentUser.fullName = [profile.first_name, profile.surname].join(' ').trim();
        // service.currentUser.phone = profile.phone;

        service.storeUserToCookie();
      });
    },

    updateUserPasswords: function (passwords) {
      return $http({
        method: 'PATCH',
        // todo: Move this route to an external routing config file
        url: '/api/users/current',
        data: { change_password: passwords },
        headers: getWsseHeader(service.currentUser)
      });
    },

    confirmUser: function (token) {
      return $http({
        method: 'PATCH',
        // todo: Move this route to an external routing config file
        url: '/api/users/confirm',
        data: { token: token }
      });
    },

    storeUserToCookie: function () {
      Cookies.setItem('user_email', this.currentUser.email, 7200, '/');
      Cookies.setItem('user_token', this.currentUser.token, 7200, '/');
      Cookies.setItem('user_type', this.currentUser.type, 7200, '/');
    },

    // Is the current user authenticated?
    isAuthenticated: function () {
      if (service.currentUser === null) {
        return false;
      }

      return !!(service.currentUser.email && service.currentUser.token && service.currentUser.type);
    },

    // Is the current user an administrator?
    isAdmin: function () {
      return !!(service.currentUser && service.currentUser.admin);
    },

    getAuthHeader: function () {
      return getWsseHeader(service.currentUser);
    }
  };

  service.initialize();

  return service;
}]);
