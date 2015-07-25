// TODO: refactoring of this directive required badly (Rewrite into CoffeeScript)
angular.module('kernel').directive('textareaLimiter', ['$compile', 'Pluralizer', function ($compile, Pluralizer) {
  return {
    require: 'ngModel',
    compile: function (elem, attributes) {
      return function($scope, element, attrs, ngModel) {
        var maxLength = attrs.ngMaxlength || 140;
        var help = angular.element('<p class="help-block">{{symbolsLeft}}</p>');

        help.insertAfter(element);
        $compile(help)($scope);

        var getSymbolsLeft = function (number) {
          var label = [];

          label.push(Pluralizer.ru(number, 'Остался', 'Осталось', 'Осталось'));
          label.push(number);
          label.push(Pluralizer.ru(number, 'символ', 'символа', 'символов'));

          return label.join(' ');
        };

        var refreshSymbolLeft = function () {
          var symbolsLeft = maxLength;

          if (angular.isDefined(ngModel.$viewValue)) {
            symbolsLeft -= ngModel.$viewValue.length;
          }

          $scope.$apply(function () {
            $scope.symbolsLeft = getSymbolsLeft(symbolsLeft);
          });
        };

        // Adds a parser rule for a limitation of text length
        ngModel.$parsers.unshift(function (inputValue) {
          if (angular.isUndefined(inputValue)) {
            return;
          }

          if (maxLength - inputValue.length <= 0) {
            inputValue = inputValue.substring(0, maxLength);
          }

          ngModel.$viewValue = inputValue;
          ngModel.$render();

          return inputValue;
        });

        $scope.$watch(attributes.ngModel, function (current, old) {
          if (!current || current === old) {
            var currentLength = 0;

            if (angular.isDefined(current) && angular.isString(current)) {
              currentLength = current.length;
            }

            $scope.symbolsLeft = getSymbolsLeft(maxLength - currentLength);

            return;
          }

          ngModel.$setViewValue(current.substring(0, maxLength));
          ngModel.$render();

          $scope.symbolsLeft = getSymbolsLeft(maxLength - ngModel.$viewValue.length);
        });

        element.bind('keyup', function () {
          refreshSymbolLeft();
        });
      };
    }
  };
}]);
