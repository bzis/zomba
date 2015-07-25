angular.module('resources.campaigns', ['zmbk.config', 'security']).factory 'Campaigns', [
  '$http', '$q', 'APP.CONFIG', 'security', ($http, $q, config, security) ->
    'use strict'

    new class Campaigns
      resourceUrl: "#{config.apiPath}/campaigns"
      platformUrl: "#{config.apiPath}/platforms"

      new: (hash = null) ->
        if hash?
          deferred = $q.defer()
          campaign = getStructure()
          campaign.hash = hash
          deferred.resolve campaign
          deferred.promise
        else
          getStructure()

      all: ->
        $http.get(@resourceUrl, headers: security.getAuthHeader()).then (response) ->
          campaigns = []
          campaigns.push convertToView(campaign) for campaign in response.data
          campaigns

      allActive: ->
        $http.get(@resourceUrl, headers: security.getAuthHeader()).then (response) ->
          campaigns = []
          campaigns.push convertToView(campaign) for campaign in response.data when campaign.status is 'on'
          campaigns

      getById: (id) ->
        url = "#{@resourceUrl}/#{id}"
        $http.get(url, headers: security.getAuthHeader()).then (response) ->
          convertToView response.data

      create: (campaign) ->
        apiCampaign = convertToApi campaign
        campaignData = campaign: apiCampaign
        $http.put @resourceUrl, campaignData, headers: security.getAuthHeader()

      update: (campaign) ->
        url = "#{@resourceUrl}/#{campaign.id}"
        apiCampaign = convertToApi campaign
        # During the campaign updating we have to remove the "statistics" data,
        # otherwise API returns a validation error
        delete apiCampaign.statistics
        campaignData = campaign: apiCampaign
        $http.put url, campaignData, headers: security.getAuthHeader()

      updateLocked: (campaign) ->
        url = "#{@resourceUrl}/#{campaign.id}"
        campaignData =
          campaign:
            name: campaign.title
            description: campaign.description
        $http.put url, campaignData, headers: security.getAuthHeader()

      updateStatus: (campaignId, status) ->
        url = "#{@resourceUrl}/#{campaignId}/status"
        campaignData =
          campaign:
            status: status
        $http.put url, campaignData, headers: security.getAuthHeader()

      getByPlatformId: (platformId, countryId = null, page = 1, perPage = 10) ->
        url = "#{@platformUrl}/#{platformId}/campaigns?page=#{page}&per_page=#{perPage}"
        url += "&countries[]=#{countryId}" if countryId?
        $http.get(url, headers: security.getAuthHeader()).then (response) ->
          paginator = response.headers()['link']
          reply =
            paginator: paginator
            campaigns: []
          reply.campaigns.push convertToView(campaign) for campaign in response.data
          reply

      enableByPlatformId: (campaignId, platformId) ->
        url = "#{@platformUrl}/#{platformId}/ban/#{campaignId}"
        $http.delete url, headers: security.getAuthHeader()

      disableByPlatformId: (campaignId, platformId) ->
        url = "#{@platformUrl}/#{platformId}/ban/#{campaignId}"
        $http.put url, {}, headers: security.getAuthHeader()

      # private

      getStructure = ->
        {
          id: null
          hash: null
          hashId: null
          title: ''
          description: ''
          countries: []
          tags: []
          gender: null
          ages: []
          balance: 0
          totalBudget: 1000
          dailyBudget: 100
          remainingAmount: 1000
          budgetRatio:
            left: 100
            right: 0
          maxBid: 2
          views: 1000
          status: ''
          statusHuman: 'не установлен'
          isBanned: false
          previewUrl: ''
          createdAt: null
          statistics:
            uploaded: null
            duration: 0
            likes: 0
            dislikes: 0
            rating: 0
            views: 0
            favorites: 0
            comments: 0
            updatedAt: null
          socialActivity:
            fb:
              likes: 0
              shares: 0
            vk:
              likes: 0
              shares: 0
            gplus:
              likes: 0
              shares: 0
            total:
              likes: 0
              shares: 0
            updatedAt: null
        }

      humanizeStatus = (status) ->
        switch status
          when 'on' then 'активна'
          when 'paused' then 'приостановлена'
          when 'ended' then 'завершена'
          when 'awaiting' then 'в ожидании'
          when 'archived' then 'в архиве'
          else 'не установлен'

      convertToView = (apiCampaign) ->
        campaign = getStructure()
        campaign.id = apiCampaign.id
        campaign.hash = apiCampaign.hash
        campaign.hashId = apiCampaign.hash_id
        campaign.title = apiCampaign.name
        campaign.description = apiCampaign.description
        campaign.countries.push parseInt(country.id, 10) for country in apiCampaign.countries
        campaign.tags = apiCampaign.tags
        campaign.gender = apiCampaign.gender
        campaign.ages.push parseInt(age.id, 10) for age in apiCampaign.age_ranges
        campaign.balance = apiCampaign.balance
        campaign.totalBudget = apiCampaign.general_budget
        campaign.dailyBudget = apiCampaign.daily_budget
        campaign.maxBid = apiCampaign.bid
        campaign.views = apiCampaign.total_views
        campaign.paidViews = apiCampaign.paid_views
        campaign.status = apiCampaign.status
        campaign.statusHuman = humanizeStatus(apiCampaign.status)
        campaign.isBanned = apiCampaign.banned? and apiCampaign.banned is true
        campaign.previewUrl = "//img.youtube.com/vi/#{campaign.hash}/hqdefault.jpg"
        campaign.createdAt = apiCampaign.created_at
        if apiCampaign.general_budget_remains?
          campaign.remainingAmount = apiCampaign.general_budget_remains
          if apiCampaign.general_budget is 0
            campaign.budgetRatio.left = 0
          else
            campaign.budgetRatio.left = Math.ceil(apiCampaign.general_budget_remains / apiCampaign.general_budget * 100)
          campaign.budgetRatio.right = 100 - campaign.budgetRatio.left
        if apiCampaign.youtube_data? and apiCampaign.youtube_data isnt 'null'
          campaign.statistics = apiCampaign.youtube_data
        if apiCampaign.social_data? and apiCampaign.social_data isnt 'null'
          campaign.socialActivity = getSocialActivity(apiCampaign)
        campaign

      getSocialActivity = (apiCampaign) ->
        socialActivity = {}
        socialActivity.fb =
          likes: apiCampaign.social_data.fbLikes
          shares: apiCampaign.social_data.fbShares
        socialActivity.vk =
          likes: apiCampaign.social_data.vkLikes
          shares: apiCampaign.social_data.vkShares
        # This may seem bizarre, but the key "gplusShares" shows g+ likes
        socialActivity.gplus =
          likes: apiCampaign.social_data.gplusShares
          shares: 0
        socialActivity.total =
          likes: socialActivity.fb.likes +
                 socialActivity.vk.likes +
                 socialActivity.gplus.likes
          shares: socialActivity.fb.shares +
                  socialActivity.vk.shares +
                  socialActivity.gplus.shares
        socialActivity.updatedAt = apiCampaign.social_data.updatedAt
        socialActivity

      convertToApi = (campaign) ->
        apiCampaign =
          hash: campaign.hash
          name: campaign.title
          description: campaign.description
          countries: campaign.countries
          tags: campaign.tags.join ','
          ageRanges: campaign.ages
          bid: campaign.maxBid
          generalBudget: campaign.totalBudget
          dailyBudget: campaign.dailyBudget
          statistics: campaign.statistics
        campaign.gender = '' if campaign.gender isnt 'male' and campaign.gender isnt 'female'
        apiCampaign.gender = campaign.gender
        apiCampaign
]
