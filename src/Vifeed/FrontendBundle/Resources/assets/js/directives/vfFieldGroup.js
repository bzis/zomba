/**
 * Created by vadim on 12/4/13.
 */

'use strict';

advertiserApp.directive('vfGroupField', function () {
    return {
        restrict: 'E',
        require: '^form',
        replace: true,
        transclude: true,
        template: '<div class="form-group" ng-transclude></div>',
        link: function ($scope, element, attrs, controller) {
            var formName = controller.$name;
            var fieldName = attrs['for'];
            var watchExpression = getFieldValidationExpression(formName, fieldName);

            $scope.$watch(watchExpression, function () {
                var field = $scope[formName][fieldName];

                if (field.$pristine) {
                    return;
                }

                var hasError = false;
                var errors = field.$error;

                for (var error in errors) {
                    if (errors.hasOwnProperty(error)) {
                        if (errors[error]) {
                            hasError = true;
                            break;
                        }
                    }
                }

                if (hasError) {
                    element.addClass('error');
                } else {
                    element.removeClass('error');
                }
            });

            function getFieldValidationExpression(formName, fieldName) {
                var fieldExpression = formName + '.' + fieldName;
                var invalidExpression = fieldExpression + '.$invalid';
                var dirtyExpression = fieldExpression + '.$dirty';

                return invalidExpression + ' && ' + dirtyExpression;
            }
        }
    }
});