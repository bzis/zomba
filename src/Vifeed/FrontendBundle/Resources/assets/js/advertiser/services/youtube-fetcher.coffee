angular.module('youtubeFetcher', []).factory 'YoutubeFetcher', [
  '$http', ($http) ->
    'use strict'

    new class YoutubeFetcher
      fetch: (videoHash) ->
        $http.get("http://gdata.youtube.com/feeds/api/videos/#{videoHash}?v=2&alt=json")
        .then (response) -> response.data
]
