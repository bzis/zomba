angular.module('kernel').directive('passwordMatch', function () {
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function ($scope, element, attrs, ngModel) {
      var validateEqual = function (myValue, otherValue) {
        if (myValue === otherValue) {
          ngModel.$setValidity('equal', true);

          return myValue;
        }

        ngModel.$setValidity('equal', false);

        return myValue;
      };

      $scope.$watch(attrs.passwordMatch, function (otherModelValue) {
        validateEqual(ngModel.$viewValue, otherModelValue);
      });

      ngModel.$parsers.unshift(function (viewValue) {
        return validateEqual(viewValue, $scope.$eval(attrs.passwordMatch));
      });

      ngModel.$formatters.unshift(function (modelValue) {
        return validateEqual(modelValue, $scope.$eval(attrs.passwordMatch));
      });
    }
  };
});
