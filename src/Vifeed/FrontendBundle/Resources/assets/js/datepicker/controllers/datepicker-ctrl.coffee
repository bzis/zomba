angular.module('datepicker').controller 'DatepickerCtrl', [
  '$scope', '$window', '$routeParams', '$location',
  ($scope, $window, $routeParams, $location) ->
    moment = $window.moment
    dateFrom = moment().subtract(30, 'days')
    dateTo = moment()

    dateFrom = moment($routeParams.dateFrom) if $routeParams.dateFrom?
    dateTo = moment($routeParams.dateTo) if $routeParams.dateTo?

    $scope.period = startDate: dateFrom, endDate: dateTo

    $scope.info =
      spendings: 'За указанный период расходы отсутствуют',
      payments: 'За указанный период платежей не поступало',
      earnings: 'За указанный период доходы отсутствуют',
      withdrawals: 'За указанный период средства не выводились'

    $scope.ranges =
      'Сегодня': [moment(), moment()],
      'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Последние 7 дней': [moment().subtract(7, 'days'), moment()],
      'Последние 30 дней': [moment().subtract(30, 'days'), moment()],
      'Этот месяц': [moment().startOf('month'), moment().endOf('month')]

    $scope.translation =
      fromLabel: 'С:',
      toLabel: 'До:',
      customRangeLabel: 'Изменить диапазон',
      applyLabel: '<i class="fa fa-check"></i>',
      cancelLabel: '<i class="fa fa-times"></i>'

    $scope.changePeriod = (prefix) ->
      url = prefix +
            '/' + $scope.period.startDate.format('YYYY-MM-DD') +
            '/' + $scope.period.endDate.format('YYYY-MM-DD')
      $location.path url

    $scope.changeDetailPeriod = (prefix) ->
      url = prefix + '/' + $routeParams.id +
            '/details/' + $scope.period.startDate.format('YYYY-MM-DD') +
            '/' + $scope.period.endDate.format('YYYY-MM-DD')
      $location.path url
  ]
