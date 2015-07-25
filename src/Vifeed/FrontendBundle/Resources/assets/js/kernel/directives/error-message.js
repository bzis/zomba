// TODO: Rewrite into CoffeeScript
angular.module('kernel').directive('errorMessage', function () {
  return {
    restrict: 'E',
    require: '^form',
    replace: true,
    template: '<p class="help-block"></p>',
    link: function ($scope, element, attrs, controller) {
      var watchExpression = controller.$name + '.$submitted';
      var field = $scope.$eval(controller.$name + '.' + attrs['for']);
      var $field = angular.element('[name=' + attrs['for'] + ']');

      var toggleStateOfValidation = function () {
        if (element.is(':visible')) {
          element.hide().parent().removeClass('has-error');
        }

        if (field.$valid) {
          element.parent().addClass('has-success').removeClass('has-error');
          controller.$submitted = false;
        } else {
          element.parent().removeClass('has-success');
        }
      };

      var showErrorMessages = function () {
        var hasErrors = field.$invalid;

        if (!hasErrors) {
          return;
        }

        var errors = field.$error,
            html = [],
            errorDisplayed = false;

        angular.forEach(errors, function (value, name) {
          // Angular.forEach does not support "break/continue"
          if (!errorDisplayed && value) {
            html.push('<span>');
            html.push(attrs[name]);
            html.push('</span>');

            errorDisplayed = true;
          }
        });

        element.html(html.join('')).show()
          .parent().addClass('has-error').removeClass('has-success');
      };

      $field.bind('focusout', showErrorMessages);

      $scope.$watch(controller.$name + '.' + attrs['for'] + '.$modelValue', function (currentValue, oldValue) {
        if (currentValue != oldValue) {
          toggleStateOfValidation();
        }
      });

      $scope.$watch(watchExpression, function (value) {
        if (controller.$submitted === true && field.$invalid) {
          showErrorMessages();
        }
      });
    }
  };
});
