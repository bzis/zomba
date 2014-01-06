/**
 * Created by vadim on 12/10/13.
 */

'use strict';

advertiserApp.directive('vfVideoPreview', ['$sce', function ($sce) {
    return {
        restrict: 'E',
        require: '^form',
        replace: true,
        template: '<iframe frameborder="0" allowfullscreen></iframe>',
        link: function ($scope, element, attrs) {
            var model = $scope[attrs.ngModel],
                requiredProperties = ['width', 'height', 'hash'];

            if (!angular.isObject(model)) {
                throw new Error('The object must be passed to the campaignVideoPreview directive');
            }

            for (var i in requiredProperties) {
                if (!model.hasOwnProperty(requiredProperties[i])) {
                    throw new Error('The object passed to the campaignVideoPreview directive must have property "' + requiredProperties[i] + '"');
                }
            }

            var src = '//www.youtube.com/embed/' + model.hash;

            $sce.trustAsUrl(src);

            element.attr('width', +model.width);
            element.attr('height', +model.height);
            element.attr('src', src);
        }
    }
}]);