$(document).ready ($) ->
  youtubeAnimation = ->
    youtube = Snap.select('#youtube')
    youtubeCircle = youtube.select('circle')
    youtubePlayIcon = youtube.select('#youtubePlayIcon')
    youtubeBtn = youtube.select('#youtubeBtn')
    youtubeBtnShadow = youtube.select('#youtubeBtnShadow')

    youtubeCircle.animate
      r: 90
    , 1000, mina.easein, ->
      youtubeBtn.animate
        transform: 't-147,0'
      , 300, mina.easeout, ->
        youtubePlayIcon.animate
          transform: 't115,0'
        , 300, ->
          youtubeBtnShadow.animate
            opacity: 1
          , 500, mina.easeout

  targetAnimation = ->
    target = Snap.select('#target')
    targetCircle1 = target.select('#circle1')
    targetCircle2 = target.select('#circle2')
    targetCircle3 = target.select('#circle3')
    targetCircle4 = target.select('#circle4')
    targetCircle5 = target.select('#circle5')
    targetShadow = target.select('#targetShadow')
    targetArrow = target.select('#targetArrow')

    targetCircle1.animate
      r: 90
    , 200, mina.easein, ->
      targetCircle2.animate
        r: 72
      , 200, mina.easein, ->
        targetCircle3.animate
          r: 54
        , 200, mina.easein, ->
          targetCircle4.animate
            r: 36
          , 200, mina.easein, ->
            targetCircle5.animate
              r: 18
            , 200, mina.easein, ->
              targetArrow.animate
                transform: 't90,90'
              , 100, ->
                targetShadow.animate
                  opacity: 0.15
                , 500, mina.easeout

  eyeAnimation = ->
    face = Snap.select('#face')
    eye = face.select('#eye')
    pupil = face.select('#pupil')
    pupilCircle = pupil.select('circle')
    umbrella = pupil.select('#umbrella')
    zombakkaLogo = pupil.select('#zombakka-logo')
    eyeCircle = face.select('#eyeCircle')
    hair = face.select('#hair')
    pupilShadow = face.select('#pupilShadow')
    eyeShadow = face.select('#eyeShadow')
    faceShadow = face.select('#faceShadow')
    eyeCircle.animate
      r: 90
    , 1000, mina.easein, ->
      eye.animate
        transform: 't163,0'
      , 300, ->
        pupilCircle.animate
          r: 30.285
        , 700, ->
          umbrella.animate
            opacity: 1
            transform: 'r-360,-72,90'
          , 700
          zombakkaLogo.animate
            opacity: 1
            transform: 'r360,-72,90'
          , 700
          hair.animate
            opacity: 1
          , 500, ->
            eyeShadow.animate
              fill: '#C3E5F9'
            , 1000
            pupilShadow.animate
              opacity: 0.15
            , 1000
            faceShadow.animate
              opacity: 1
            , 1000

  # init controller
  controller = new ScrollMagic()
  scene = new ScrollScene(
    triggerElement: '#how-its-work-animation-trigger'
    duration: 200
  ).addTo(controller).on('enter leave', (e) ->
    $('.pulse-arrow').hide()
    youtubeAnimation()
    targetAnimation()
    eyeAnimation()
    $('.how-its-work').addClass('on')
  )
  $("#scene-few-statistics").parallax()
  # build scene
  # .on("update", function (e) {
  #   $("#scrollDirection").text(e.target.parent().info("scrollDirection"));
  # })
  # .on("enter leave", function (e) {
  # 	$("#state").text(e.type == "enter" ? "inside" : "outside");
  # })
  scene = new ScrollScene(
    triggerElement: "#statistics-chart-animation-trigger"
    duration: 200
  ).addTo(controller).on("enter leave", (e) ->
    if e.type is "enter"
      $(".chart").easyPieChart
        #your configuration goes here
        animate: 3000
        size: 180
        scaleLength: 0
        lineWidth: 5

      $(".chart-odometer").each ->
        chartOdometer = new Odometer(
          el: this
          theme: "minimal"
          value: "0"
        )
        chartOdometer.render()
        chartOdometer.update $(this).data("new-value")
  )

  $('#scene').parallax() if $("#scene")
  $('#scene2').parallax() if $("#scene2")
  $("#promo-video").fitVids() if $("#promo-video")

  skrollr.init
    forceHeight: false
    mobileCheck: ->
      #hack - forces mobile version to be off
      $("body").addClass "mobile-view" if navigator.userAgent.match(/iPhone|iPod|iPad|Android|Windows Phone/i)?
      false
