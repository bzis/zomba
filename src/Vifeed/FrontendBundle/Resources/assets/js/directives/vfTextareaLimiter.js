/**
 * Created by vadim on 12/13/13.
 */

'use strict';

advertiserApp.directive('vfTextareaLimiter', ['PluralizerService', function (PluralizerService) {
    return {
        require: 'ngModel',
        compile: function (elem, attributes) {
            return function($scope, element, attrs, ngModel) {
                var maxLength = attrs.ngMaxlength || 140;
                    //symbolsLeft = maxLength;

                var getSymbolsLeft = function (number) {
                    var label = [];

                    label.push(PluralizerService.pluralize(number, 'Остался', 'Осталось', 'Осталось'));
                    label.push(number);
                    label.push(PluralizerService.pluralize(number, 'символ', 'символа', 'символов'));

                    return label.join(' ');
                };

                var refreshSymbolLeft = function () {
                    var symbolsLeft = maxLength - ngModel.$viewValue.length;

                    $scope.$apply(function () {
                        $scope.symbolsLeft = getSymbolsLeft(symbolsLeft)
                    });
                };

                var unwatch = $scope.$watch(
                    attributes.ngModel,
                    function(current, old) {
                        if (!current || current === old) {
                            return;
                        }

                        ngModel.$viewValue = current.substring(0, maxLength);
                        ngModel.$render();

                        $scope.symbolsLeft = getSymbolsLeft(maxLength - ngModel.$viewValue.length);
                        unwatch();
                    },
                    true
                );

                $scope.symbolsLeft = getSymbolsLeft(maxLength);

                element.bind("keyup", function (event) {
                    ngModel.$parsers.unshift(function (inputValue) {
                        if (maxLength - inputValue.length <= 0) {
                            inputValue = inputValue.substring(0, maxLength);
                        }

                        ngModel.$viewValue = inputValue;
                        ngModel.$render();

                        return inputValue;
                    });

                    refreshSymbolLeft();
                });
            }
        }
    }
}]);
