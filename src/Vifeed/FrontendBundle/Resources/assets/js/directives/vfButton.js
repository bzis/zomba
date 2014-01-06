/**
 * Created by vadim on 12/5/13.
 */

'use strict';

advertiserApp.directive('vfButton', ['$location', function ($location) {
    return {
        restrict: 'E',
        require: '^form',
        transclude: true,
        replace: true,
        template: '<button class="btn btn-primary btn-block" type="submit" ng-click="goToSetupCampaign()" ng-transclude></button>',
        link: function ($scope, element, attrs, controller) {
            var watchExpression = controller.$name + '.$invalid';

            $scope.$watch(watchExpression, function (value) {
                attrs.$set('disabled', !!value);
            });

            $scope.goToSetupCampaign = function () {
                var campaignHash = $scope.campaign.link.replace('http://www.youtube.com/watch?v=', '');

                $location.path('/campaign-settings/' + campaignHash);
            };
        }
    };
}]);