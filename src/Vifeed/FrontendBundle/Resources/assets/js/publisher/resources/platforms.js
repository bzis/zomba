angular.module('resources.platforms', []).factory('Platforms',
  ['$http', '$q', '$location', 'APP.CONFIG', 'security', function ($http, $q, $location, config, security) {
    var TYPE_SITE = 'site',
        TYPE_VK = 'vk';

    var platforms = {
          TYPE_SITE: TYPE_SITE,
          TYPE_VK: TYPE_VK
        },
        resourceUrl = [config.apiPath, '/platforms'].join('');

    // Private interface

    // Gets a default platform structure
    var getStructure = function () {
      return {
        id: null,
        title: '',
        url: '',
        description: '',
        type: TYPE_SITE,
        countries: [],
        tags: [],
        vk_id: ''
      };
    };

    // Public interface

    // Gets a new object of a platform
    platforms.new = function () {
      return getStructure();
    };

    // Creates a new platform
    platforms.create = function (platform) {
      var platformData = {
        platform: {
          name: platform.title,
          url: platform.url,
          description: platform.description,
          type: platform.type,
          countries: platform.countries,
          tags: platform.tags.join(','),
          vkId: platform.vk_id
        }
      };

      if (platform.type === TYPE_SITE) {
        delete platformData.platform.vkId;
      }

      return $http.put(
        resourceUrl,
        platformData,
        { headers: security.getAuthHeader() }
      );
    };

    // Gets a platform by its identificator
    platforms.getById = function (platformId) {
      var url = [resourceUrl, '/', platformId].join('');

      return $http.get(
        url,
        { headers: security.getAuthHeader() }
      ).then(function (response) {
        return response.data;
      });
    };

    // Gets all user's platforms
    platforms.all = function () {
      return $http.get(
        resourceUrl,
        { headers: security.getAuthHeader() }
      ).then(function (response) {
        return response.data;
      });
    };

    // Removes the platform by its id
    platforms.delete = function (id) {
      var url = [resourceUrl, '/', id].join('');

      return $http.delete(url, { headers: security.getAuthHeader() });
    };

    return platforms;
  }]
);
