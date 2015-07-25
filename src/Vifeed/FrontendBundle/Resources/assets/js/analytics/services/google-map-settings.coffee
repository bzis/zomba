angular.module('analytics').factory 'GoogleMapSettings', ->
  new class GoogleMapSettings
    getMapStyles: ->
      [{
        'stylers': [{ 'visibility': 'on' },]
      }, {
        'featureType': 'road',
        'stylers': [{ 'visibility': 'on' }, { 'color': '#ffffff' }]
      }, {
        'featureType': 'road.arterial',
        'stylers': [{ 'visibility': 'on' }, { 'color': '#fee379' }]
      }, {
        'featureType': 'road.highway',
        'stylers': [{ 'visibility': 'on' }, { 'color': '#fee379' }]
      }, {
        'featureType': 'landscape',
        'stylers': [{ 'visibility': 'on' }, { 'color': '#f3f4f4' }]
      }, {
        'featureType': 'water',
        'stylers': [{ 'visibility': 'on' }, { 'color': '#35cde7' }]
      }, {}, {
        'featureType': 'road',
        'elementType': 'labels',
        'stylers': [{ 'visibility': 'off' }]
      }, {
        'featureType': 'poi.park',
        'elementType': 'geometry.fill',
        'stylers': [{ 'visibility': 'off' }, { 'color': '#83cead' }]
      }, {
        'elementType': 'labels',
        'stylers': [{ 'visibility': 'on' }]
      }, {
        'featureType': 'landscape.man_made',
        'elementType': 'geometry',
        'stylers': [{ 'weight': 0.9 }, { 'visibility': 'off' }]
      }]

    getMapSettings: ->
      center:
        latitude: 23.520687 #55.7500,
        longitude: 3.163575 #37.6167
      zoom: 2
      options:
        disableDefaultUI: false
        mapTypeControl: false
        scaleControl: true
        navigationControl: false
        streetViewControl: false
        disableDoubleClickZoom: false
        minZoom: 2
        maxZoom: 8
        styles: @getMapStyles()
      control: {}
      events: {}

    getHeatMapSettings: ->
      options:
        radius: 20
        opacity: 0.8
        gradient: [
          # 'rgba(23, 141, 126, 0)',
          # 'rgba(23, 141, 126, 1)',
          # 'rgba(255, 0, 0, 1)'
          'rgba(0, 255, 255, 0)'
          'rgba(0, 255, 255, 1)'
          'rgba(0, 191, 255, 1)'
          'rgba(0, 127, 255, 1)'
          'rgba(0, 63, 255, 1)'
          'rgba(0, 0, 255, 1)'
          'rgba(0, 0, 223, 1)'
          'rgba(0, 0, 191, 1)'
          'rgba(0, 0, 159, 1)'
          'rgba(0, 0, 127, 1)'
          'rgba(63, 0, 91, 1)'
          'rgba(127, 0, 63, 1)'
          'rgba(191, 0, 31, 1)'
          'rgba(255, 0, 0, 1)'
        ]
