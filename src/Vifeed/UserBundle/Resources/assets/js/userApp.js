var userApp = angular.module('userApp', ['app', 'ngRoute', 'ui.bootstrap.tabs', 'templates-angularUiBootstrapTabs']);
//, function($interpolateProvider) {
    // $interpolateProvider.startSymbol('{[{');
    // $interpolateProvider.endSymbol('}]}');
//})


userApp.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/', {
            templateUrl: '/bundles/vifeeduser/partials/sign-up.html', 
            controller: SignupController
        });
}]);