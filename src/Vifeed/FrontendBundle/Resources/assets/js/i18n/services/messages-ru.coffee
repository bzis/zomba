angular.module('i18n.messages.ru', []).factory 'MessagesRu', ->
  'use strict'

  {
    'login.reason.notAuthorized': "У вас нет необходимых прав доступа. Вы хотите войти под другим аккаунтом?"
    'login.reason.notAuthenticated': "Вы должны быть авторизованы для продолжения."
    'login.error.invalidCredentials': "Неверный логин или пароль."
    'login.error.serverError': "Возникла проблема во время аутентификации: {{exception}}."
  }
