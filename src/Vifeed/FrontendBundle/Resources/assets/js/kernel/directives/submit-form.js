angular.module('kernel').directive('submitForm', ['$parse', function ($parse) {
  return {
    require: 'form',
    link: function ($scope, element, attrs, controller) {
      var submitFunction = $parse(attrs.submitForm);
      controller.$submitted = false;

      element.on('submit', function (event) {
        $scope.$apply(function () {
          controller.$submitted = true;

          if (controller.$valid) {
            submitFunction($scope, { $event : event });
            controller.$submitted = false;
          } else {
            $scope.isFormError = true;
          }
        });
      });
    }
  };
}]);
