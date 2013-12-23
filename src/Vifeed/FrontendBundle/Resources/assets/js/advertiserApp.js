/**
 * Created by vadim on 12/1/13.
 */

'use strict';

var advertiserApp = angular.module('advertiserApp', ['app', 'ui.mask', 'ui.select2']);

advertiserApp.constant('apiConfig', {
    campaignUrl: "/api/campaigns"
});

advertiserApp.config(['$routeProvider', function ($routeProvider) {
    $routeProvider
        .when('/', {
            templateUrl: '/bundles/vifeedfrontend/partials/campaign.html',
            controller: CampaignController
        })
        .when('/campaign-settings/:campaignHash', {
            templateUrl: '/bundles/vifeedfrontend/partials/campaign-settings.html',
            controller: CampaignSettingsController
        })
        .otherwise({ redirectTo: '/' });
}]);