// TODO rewrite to CoffeeScript
angular.module('datepicker').directive('dateRange', ['$compile', '$parse', function ($compile, $parse) {
  return {
    require: '?ngModel',
    link: function ($scope, $element, $attributes, ngModel) {
      var options = {},
          onChangeFunction = $parse($attributes.onChange)($scope) || null;

      options.format = $attributes.format || 'YYYY-MM-DD';
      options.separator = ' - ';

      if ($attributes.$attr.separator) {
        options.separator = $element.attr($attributes.$attr.separator) || ' - ';
      }

      options.minDate = $attributes.minDate && moment($attributes.minDate);
      options.maxDate = $attributes.maxDate && moment($attributes.maxDate);
      options.dateLimit = $attributes.limit && moment.duration.apply(
        this,
        $attributes.limit.split(' ').map(function (elem, index) {
          return index === 0 && parseInt(elem, 10) || elem;
        })
      );
      options.ranges = $attributes.ranges && $parse($attributes.ranges)($scope);
      options.showDropdowns = ($attributes.showDropdowns == 'true' || $attributes.showDropdowns === true);
      options.showWeekNumbers = ($attributes.showWeekNumbers == 'true' || $attributes.showWeekNumbers === true);
      options.timePicker = ($attributes.timePicker == 'true' || $attributes.timePicker === true);
      options.condenseSameDay = ($attributes.condenseSameDay == 'true' || $attributes.condenseSameDay === true);
      options.timePickerIncrement = parseInt($attributes.timePickerIncrement, 10);
      options.timePicker12Hour = ($attributes.timePicker12Hour == 'true' || $attributes.timePicker12Hour === true);
      options.opens = $attributes.opens || 'right';
      options.buttonClasses = $attributes.buttonClasses || ['btn', 'btn-small'];
      options.applyClass = $attributes.applyClass || '';
      options.cancelClass = $attributes.cancelClass || '';
      options.locale = $attributes.locale && $parse($attributes.locale)($scope)|| {};
      options.parentEl = ($attributes.parentEl && angular.element($attributes.parentEl)) || null;

      ngModel.$formatters.unshift(function (modelValue) {
        if (!modelValue) return '';

        return modelValue;
      });

      ngModel.$parsers.unshift(function (viewValue) {
        return viewValue;
      });

      $scope.$watch($attributes.ngModel, function (modelValue, oldValue) {
        if (!modelValue || (!modelValue.startDate)) {
          ngModel.$setViewValue({ startDate: moment().startOf('day'), endDate: moment().startOf('day') });

          return;
        }

        $element.data('daterangepicker').setStartDate(modelValue.startDate);
        $element.data('daterangepicker').setEndDate(modelValue.endDate);
        $element.data('daterangepicker').updateView();
        $element.data('daterangepicker').updateCalendars();
        $element.data('daterangepicker').updateInputText();
      });

      $element.daterangepicker(options, function (start, end) {
        $scope.$apply(function () {
          ngModel.$setViewValue({ startDate: start, endDate: end });

          if (angular.isFunction(onChangeFunction)) {
            onChangeFunction();
          }
        });
      });
    }
  };
}]);
