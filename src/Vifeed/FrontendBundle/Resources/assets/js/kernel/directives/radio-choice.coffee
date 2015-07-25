angular.module('kernel').directive 'radioChoice', ->
  {
    restrict: 'E'
    require: 'ngModel'
    replace: true
    transclude: true
    template: "<div><label class=\"checkbox-inline\">
              <input type=\"checkbox\" checked=\"checked\">Все &nbsp;</label>
              <span ng-transclude></span></div>"
    link: ($scope, element, attrs, ngModel) ->
      checkBox = element.find('input[type=checkbox]')
      radios = element.find('input[type=radio]')

      $scope.$watch attrs.ngModel, (current, old) ->
        return if not current or current is old
        checkBox.attr('checked', false)
        element.find("input[value=#{current}]").attr('checked', true)

      if attrs.ngDisabled
        $scope.$watch attrs.ngDisabled, (current) ->
          if current
            checkBox.attr('disabled', true)
          else
            checkBox.removeAttr('disabled')

      radios.bind 'click', ->
        radio = angular.element(this)
        checkBox.attr('checked', false)
        $scope.$apply ->
          ngModel.$setViewValue radio.val()
          ngModel.$render()

      checkBox.bind 'click', ->
        radios.attr('checked', false)
        $scope.$apply ->
          ngModel.$setViewValue null
          ngModel.$render()
  }
