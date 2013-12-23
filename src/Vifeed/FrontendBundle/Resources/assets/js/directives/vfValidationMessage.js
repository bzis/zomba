/**
 * Created by vadim on 12/5/13.
 */

'use strict';

advertiserApp.directive('vfValidationMessage', function () {
    return {
        restrict: 'E',
        require: '^form',
        replace: true,
        template: '<p class="help-block"></p>',
        link: function ($scope, element, attrs, controller) {
            var formName = controller.$name;
            var fieldName = attrs['for'];
            var watchExpression = getFieldValidationExpression(formName, fieldName);

            $scope.$watch(watchExpression, function () {
                var field = $scope[formName][fieldName];
                var show = field.$invalid && field.$dirty;
                var html = [];

                if (show) {
                    var errors = field.$error;

                    for (var error in errors) {
                        if (errors.hasOwnProperty(error) && attrs.hasOwnProperty(error)) {
                            html.push('<span>');
                            html.push(attrs[error]);
                            html.push('</span>');
                        }
                    }

                    element.show().parent().addClass('has-error').removeClass('has-success');
                } else {
                    element.hide().parent().removeClass('has-error').addClass('has-success');
                }

                element.html(html.join(''));
            });

            function getFieldValidationExpression(formName, fieldName) {
                var fieldExpression = formName + '.' + fieldName;
                var invalidExpression = fieldExpression + '.$invalid';
                var dirtyExpression = fieldExpression + '.$dirty';

                return invalidExpression + ' && ' + dirtyExpression;
            }
        }
    };
});