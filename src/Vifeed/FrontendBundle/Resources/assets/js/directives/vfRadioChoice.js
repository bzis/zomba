/**
 * Created by vadim on 12/16/13.
 */

'use strict';

advertiserApp.directive('vfRadioChoice', function() {
    return {
        restrict: 'E',
        require: '^form',
        replace: true,
        transclude: true,
        template: '<div><label class="checkbox-inline">' +
            '<input type="checkbox" value="all" checked="checked" />Все &nbsp;</label>' +
            '<span ng-transclude></span></div>',
        link: function($scope, element, attrs) {
            var checkBox = element.find('input[type=checkbox]'),
                radios = element.find('input[type=radio]');

            radios.bind('click', function () {
                checkBox.removeProp('checked');
            });

            checkBox.bind('click', function () {
                radios.removeProp('checked');
            });
        }
    }
});
