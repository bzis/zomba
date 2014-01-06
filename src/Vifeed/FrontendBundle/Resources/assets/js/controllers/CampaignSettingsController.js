/**
 * Created by vadim on 12/1/13.
 */

'use strict';

var CampaignSettingsController = function ($scope, $http, $routeParams) {
    $scope.video = {
        hash: $routeParams.campaignHash,
        width: 560,
        height: 315
    };

    $scope.campaign = {
        hash: $routeParams.campaignHash,
        title: '',
        description: '',
        totalBudget: 1000,
        dailyBudget: 100,
        maxBid: 1,
        views: 0
    };

    var loadCampaignDefaultSettings = function ($scope, $routeParams) {
        $http({
                method: 'GET',
                url: 'http://gdata.youtube.com/feeds/api/videos/' + $routeParams.campaignHash + '?v=2&alt=json'
            })
            .success(function (data, status, headers, config) {
                $scope.campaign.title = data.entry.title.$t;
                $scope.campaign.description = data.entry.media$group.media$description.$t;
            })
            .error(function (data, status, headers, config) {
                console.log(data, status);
            });
    };

    $scope.buildBidOptimizer = function () {
        var lowRange = 1,
            highRange = 2.5,
            bidList = [];

        for (var i = lowRange; i <= highRange; i += .1) {
            var bid = parseInt(i * 10, 10),
            // TODO:: create correct formula
                reach = bid;

            bidList.push({
                bid: bid,
                reach: reach,
                dailyBudget: bid * reach
            });
        }

        $scope.bidList = bidList;
        $scope.campaign.views = parseInt($scope.campaign.totalBudget / $scope.campaign.maxBid, 10);
    };

    loadCampaignDefaultSettings($scope, $routeParams);

    $scope.buildBidOptimizer();
};