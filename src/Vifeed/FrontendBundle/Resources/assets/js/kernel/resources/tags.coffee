angular.module('resources.tags', ['zmbk.config', 'security']).factory 'Tags', [
  '$http', '$q', 'APP.CONFIG', 'security',
  ($http, $q, config, security) ->
    new class Tags
      resourceUrl: config.apiPath + '/tags'
      loadedTags: [],
      loadedWords: []

      getOptions: (options) ->
        defaults =
          simple_tags: true,
          tags: [],
          multiple: true,
          maximumInputLength: 16,
          tokenSeparators: [',', ' ']
        angular.extend defaults, options
        defaults

      loadByWord: (word) ->
        @loadTagsByWord(word)

      loadTagsByWord: (word) ->
        url = @resourceUrl + '/' + word

        if word.length == 1 or @loadedWords.indexOf(word) >= 0
          deferred = $q.defer()
          deferred.resolve @loadedTags
          return deferred.promise

        return $http.get(url, headers: security.getAuthHeader()).then(
          (response) =>
            tags = response.data
            @loadedWords.push word if @loadedWords.indexOf(word) == -1
            if !angular.isArray(tags) or !tags.length
              return @loadedTags
            for tag in tags
              @loadedTags.push tag if @loadedTags.indexOf(tag) == -1
            @loadedTags
        )
]
