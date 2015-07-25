# Actually it has been written in a fast mode:) so I need to refactor this service
angular.module('resources.vk', ['zmbk.config']).factory 'Vk', [
  '$window', '$q', 'APP.CONFIG', ($window, $q, config) ->
    'use strict'

    new class VK
      # Read more: http://vk.com/pages?oid=-1&p=%D0%9F%D1%80%D0%B0%D0%B2%D0%B0_%D0%BF%D1%80%D0%B8%D0%BB%D0%BE%D0%B6%D0%B5%D0%BD%D0%B8%D0%B9
      FLAG_WALL_LINK = 512
      FLAG_WALL_POST = 8192
      FLAG_GROUP = 262144
      FLAG_STATS = 1048576

      constructor: ->
        @flags = FLAG_WALL_LINK + FLAG_WALL_POST + FLAG_GROUP + FLAG_STATS
        $window.VK.init apiId: config.vkKey

      # Tries to confirm access to the VK public group
      confirmGroupAccess: (groupUrl) ->
        deferred = $q.defer()
        $window.VK.Auth.login(((response) ->
          if response.session? and response.status? and response.status is 'connected'
            params =
              uid: response.session.user.id
              extended: 1
              filter: 'admin'
            $window.VK.Api.call 'groups.get', params, (group) ->
              confirmation = confirmed: false
              unless group.response?
                confirmation.message = 'Не удалось получить ответ от VKontakte API'
                deferred.reject confirmation
                return
              groupId = confirm(groupUrl, group.response.slice(1))
              if groupId
                deferred.resolve(confirmed: true, vk_id: groupId)
              else
                confirmation.message = "Убедитесь, что вы являетесь администратором
                                        группы, а так же дали необходимый доступ приложению Zombakka."
                deferred.reject(confirmation)
          ),
          @flags
        )
        deferred.promise

      # private

      confirm = (url, groups = []) ->
        for group in groups
          if url.indexOf(group.screen_name) >= 0 and group.is_admin is 1
            return group.gid
          else
            matches = url.match /\d+$/i
            continue unless matches?
            if group.screen_name.indexOf(matches[0]) >= 0 and group.is_admin is 1
              return group.gid
        return false
]
