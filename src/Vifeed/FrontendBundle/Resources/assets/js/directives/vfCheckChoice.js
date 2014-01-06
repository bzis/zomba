/**
 * Created by vadim on 12/17/13.
 */

'use strict';

advertiserApp.directive('vfCheckChoice', function() {
    return {
        restrict: 'E',
        require: '^form',
        replace: true,
        transclude: true,
        template: '<div><label class="checkbox-inline">' +
            '<input type="checkbox" class="check-all" checked="checked" />Все &nbsp;</label>' +
            '<span ng-transclude></span></div>',
        link: function($scope, element, attrs) {
            var checkAllBox = element.find('.check-all'),
                checkBoxes = element.find('input[type=checkbox]:not(.check-all)');

            checkBoxes.bind('click', function () {
                checkAllBox.removeProp('checked');
            });

            checkAllBox.bind('click', function () {
                checkBoxes.removeProp('checked');
            });
        }
    }
});
