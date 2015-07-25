class VkMock
  create: ->
    {
      init: (config) -> config
      Auth:
        login: (cb, flags) ->
          response =
            session: user: id: 1
            status: 'connected'
          cb(response)
      Api:
        call: (name, params, cb) ->
          group =
            response: [
              2, {
                admin_level: 3
                gid: 11113333
                is_admin: 1
                is_closed: 0
                is_member: 1
                name: "Zmbk#1"
                photo: "http://vk.com/images/community_50.gif"
                photo_big: "http://vk.com/images/community_200.gif"
                photo_medium: "http://vk.com/images/community_100.gif"
                screen_name: "club11113333"
                type: "page"
              }, {
                admin_level: 3
                gid: 11112222
                is_admin: 1
                is_closed: 0
                is_member: 1
                name: "Zmbk#2"
                photo: "http://cs308321.vk.me/v3...62/775b/u6408EcBqPQ.jpg"
                photo_big: "http://cs308321.vk.me/v3...62/7758/zoocWS1hulU.jpg"
                photo_medium: "http://cs308321.vk.me/v3...62/775a/EAflcsAlocM.jpg"
                screen_name: "the_zmbk"
                type: "page"
              }, {
                admin_level: 3
                gid: 11114444
                is_admin: 1
                is_closed: 1
                is_member: 1
                name: "Zmbk#3"
                photo: "http://vk.com/images/community_50.gif"
                photo_big: "http://vk.com/images/community_200.gif"
                photo_medium: "http://vk.com/images/community_100.gif"
                screen_name: "club11114444"
                type: "event"
              }, {
                admin_level: 3
                gid: 11115555
                is_admin: 1
                is_closed: 2
                is_member: 1
                name: "Zbmk#4"
                photo: "http://vk.com/images/community_50.gif"
                photo_big: "http://vk.com/images/community_200.gif"
                photo_medium: "http://vk.com/images/community_100.gif"
                screen_name: "club11115555"
                type: "group"
              }
            ]
          cb(group)
    }
