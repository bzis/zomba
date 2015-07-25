angular.module('publisher').factory 'CampaignPaginator', [
  'Campaigns', (Campaigns) ->
    'use strict'

    new class CampaignPaginator
      result =
        campaigns: []
        paginator: []

      load: (options = {}) ->
        throw new Error('Platform id is not set') unless options.platformId?
        platformId = options.platformId
        countryId = options.countryId || null
        page = options.page || 1
        perPage = options.perPage || 10
        Campaigns.getByPlatformId(platformId, countryId, page, perPage).then (response) ->
          output =
            paginator: null
            campaigns: response.campaigns
          output.paginator = createPaginator(response.paginator, page) if response.paginator?
          output

      # private

      createPaginator = (header, currentPage) ->
        limitDelta = 2
        limitFirstPage = Math.max(currentPage - limitDelta, 1)
        limitLastPage = currentPage
        prevPage = Math.max(currentPage - 1, 1)
        nextPage = currentPage
        lastPage = currentPage
        lastLink = header.split(',').slice(-1)[0]
        if /rel\=\"last\"/.test lastLink
          matches = lastLink.match /(\&|\?)page\=(\d+)/
          lastPage = +matches[2]
          limitLastPage = Math.min(currentPage + limitDelta, lastPage)
          nextPage = Math.min(currentPage + 1, lastPage)
        paginator =
          first: 1
          last: lastPage
          next: nextPage
          previous: prevPage
          current: currentPage
          pages: [limitFirstPage..limitLastPage]
]
