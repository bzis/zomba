/**
 * Created by vadim on 12/1/13.
 */

'use strict';

//var campaignService = angular.module('CampaignService', ['ngResource', 'security']);
//
//advertiserApp.factory('Campaign', ['$http', 'apiConfig', 'Cookies', 'tokenHandler' function ($http) {
//    return $resource('phones/:phoneId.json', {}, {
//        query: {method:'GET', params:{phoneId:'phones'}, isArray:true}
//    });
//}]);

advertiserApp.service('CampaignService', function ($http, apiConfig) {
    this.getUserCampaigns = function () {
        console.log('user campaigns');
//        return $http({
//                method: 'GET',
//                url: apiConfig.campaignUrl,
//                headers: {
//                    'X-WSSE': tokenHandler.getCredentials(
//                        Cookies.getItem('user_email'),
//                        Cookies.getItem('user_token')
//                    )
//                }
//            })
//            .success(function (data, status, headers, config) {
//                console.log(data);
//                //return data;
//            })
//            .error(function (data, status, headers, config) {
//                console.log('Bang!');
//                console.log(data, status, headers);
//            });
    };

    this.createUserCampaign = function () {
        console.log('create user campaign');
//        return $http({
//                method: 'PUT',
//                url: apiConfig.campaignUrl,
//                headers: {
//                    'X-WSSE': tokenHandler.getCredentials(
//                        Cookies.getItem('user_email'),
//                        Cookies.getItem('user_token')
//                    )
//                }
//            })
//            .success(function (data, status, headers, config) {
//                console.log(data);
//                //return data;
//            })
//            .error(function (data, status, headers, config) {
//                console.log('Bang!');
//                console.log(data, status, headers);
//            });
    };
});