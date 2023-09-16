// Copyright 2013 Google Inc. All Rights Reserved.
// You may study, modify, and use this example for any purpose.
// Note that this example is provided "as is", WITHOUT WARRANTY
// of any kind either expressed or implied.
// Modifed by CloudArcade
'use strict';

var Ads = function(application, videoPlayer) {
  this.application_ = application;
  this.videoPlayer_ = videoPlayer;
  this.customClickDiv_ = document.getElementById('customClick');
  this.linearAdPlaying = false;
  google.ima.settings.setVpaidMode(google.ima.ImaSdkSettings.VpaidMode.ENABLED);
  this.adDisplayContainer_ = new google.ima.AdDisplayContainer(
      this.videoPlayer_.adContainer, this.videoPlayer_.contentPlayer,
      this.customClickDiv_);
  this.adsLoader_ = new google.ima.AdsLoader(this.adDisplayContainer_);
  this.adsManager_ = null;

  this.adsLoader_.addEventListener(
      google.ima.AdsManagerLoadedEvent.Type.ADS_MANAGER_LOADED,
      this.onAdsManagerLoaded_, false, this);
  this.adsLoader_.addEventListener(
      google.ima.AdErrorEvent.Type.AD_ERROR, this.onAdError_, false, this);
};

// On iOS and Android devices, video playback must begin in a user action.
// AdDisplayContainer provides a initialize() API to be called at appropriate
// time.
// This should be called when the user clicks or taps.
Ads.prototype.initialUserAction = function() {
  this.adDisplayContainer_.initialize();
  this.videoPlayer_.contentPlayer.load();
};

Ads.prototype.requestAds = function(adTagUrl) {
  this.contentCompleteCalled = false;
  this.allAdsCompleted = false;
  var adsRequest = new google.ima.AdsRequest();
  adsRequest.adTagUrl = adTagUrl;
  adsRequest.linearAdSlotWidth = this.videoPlayer_.width;
  adsRequest.linearAdSlotHeight = this.videoPlayer_.height;
  adsRequest.nonLinearAdSlotWidth = this.videoPlayer_.width;
  adsRequest.nonLinearAdSlotHeight = this.videoPlayer_.height / 3;
  this.adsLoader_.requestAds(adsRequest);
};

Ads.prototype.pause = function() {
  if (this.adsManager_) {
    this.adsManager_.pause();
  }
};

Ads.prototype.resume = function() {
  if (this.adsManager_) {
    this.adsManager_.resume();
  }
};

Ads.prototype.resize = function(width, height) {
  if (this.adsManager_) {
    this.adsManager_.resize(width, height, google.ima.ViewMode.NORMAL);
  }
};

Ads.prototype.contentCompleted = function() {
  this.contentCompleteCalled = true;
  this.adsLoader_.contentComplete();
};

/**
 * If we're playing post-rolls, ALL_ADS_COMPLETED will not have fired at this
 * point. Here the cotent video is done, so if ads are also done, we start the
 * next video.
 */
Ads.prototype.contentEnded = function() {
  this.contentCompleted();
  if (this.allAdsCompleted) {
    this.application_.switchButtonToReplay();
  }
};

Ads.prototype.destroyAdsManager = function() {
  if (this.adsManager_) {
    this.adsManager_.destroy();
    this.adsManager_ = null;
  }
};

Ads.prototype.onAdsManagerLoaded_ = function(adsManagerLoadedEvent) {
  this.application_.log('Ads loaded.');
  var adsRenderingSettings = new google.ima.AdsRenderingSettings();
  adsRenderingSettings.restoreCustomPlaybackStateOnAdBreakComplete = true;
  this.adsManager_ = adsManagerLoadedEvent.getAdsManager(
      this.videoPlayer_.contentPlayer, adsRenderingSettings);
  this.startAdsManager_(this.adsManager_);
  if(typeof ca_api != 'undefined'){
    ca_api.on_ad_start();
  }
};

Ads.prototype.startAdsManager_ = function(adsManager) {
  if (adsManager.isCustomClickTrackingUsed()) {
    this.customClickDiv_.style.display = 'table';
  }
  // Attach the pause/resume events.
  adsManager.addEventListener(
      google.ima.AdEvent.Type.CONTENT_PAUSE_REQUESTED,
      this.onContentPauseRequested_, false, this);
  adsManager.addEventListener(
      google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED,
      this.onContentResumeRequested_, false, this);
  // Handle errors.
  adsManager.addEventListener(
      google.ima.AdErrorEvent.Type.AD_ERROR, this.onAdError_, false, this);
  adsManager.addEventListener(
      google.ima.AdEvent.Type.ALL_ADS_COMPLETED, this.onAllAdsCompleted_, false,
      this);
  adsManager.addEventListener(
      google.ima.AdEvent.Type.LOADED,
      this.onAdLoaded, false, this);
  adsManager.addEventListener(
      google.ima.AdEvent.Type.SKIPPED,
      this.onAdSkipped, false, this);
  adsManager.addEventListener(
      google.ima.AdErrorEvent.Type.Df, this.onAdError_, false, this);
  var events = [
    google.ima.AdEvent.Type.ALL_ADS_COMPLETED, google.ima.AdEvent.Type.CLICK,
    google.ima.AdEvent.Type.COMPLETE, google.ima.AdEvent.Type.FIRST_QUARTILE,
    google.ima.AdEvent.Type.LOADED, google.ima.AdEvent.Type.MIDPOINT,
    google.ima.AdEvent.Type.PAUSED, google.ima.AdEvent.Type.STARTED,
    google.ima.AdEvent.Type.THIRD_QUARTILE
  ];
  for (var index in events) {
    adsManager.addEventListener(events[index], this.onAdEvent_, false, this);
  }

  var initWidth, initHeight;
  if (this.application_.fullscreen) {
    initWidth = this.application_.fullscreenWidth;
    initHeight = this.application_.fullscreenHeight;
  } else {
    initWidth = this.videoPlayer_.width;
    initHeight = this.videoPlayer_.height;
  }
  adsManager.init(initWidth, initHeight, google.ima.ViewMode.NORMAL);

  adsManager.start();
};

Ads.prototype.onContentPauseRequested_ = function() {
  this.linearAdPlaying = true;
  this.application_.pauseForAd();
  this.application_.setVideoEndedCallbackEnabled(false);
};

Ads.prototype.onContentResumeRequested_ = function() {
  this.application_.setVideoEndedCallbackEnabled(true);
  this.linearAdPlaying = false;
  // Without this check the video starts over from the beginning on a
  // post-roll's CONTENT_RESUME_REQUESTED
  if (!this.contentCompleteCalled) {
    this.application_.resumeAfterAd();
  }
};

Ads.prototype.onAdEvent_ = function(adEvent) {
  this.application_.log('Ad event: ' + adEvent.type);

  if (adEvent.type == google.ima.AdEvent.Type.CLICK) {
    this.application_.adClicked();
  } else if (adEvent.type == google.ima.AdEvent.Type.LOADED) {
    var ad = adEvent.getAd();
    if (!ad.isLinear()) {
      this.onContentResumeRequested_();
    }
  }
  if(adEvent.type == 'complete'){
    if(typeof ca_api != 'undefined'){
      ca_api.remove_ad();
      ca_api.on_ad_finished();
    }
  }
};

Ads.prototype.onAdError_ = function(adErrorEvent) {
  this.application_.log('Ad error: ' + adErrorEvent.getError().toString());
  if (this.adsManager_) {
    this.adsManager_.destroy();
  }
  this.application_.resumeAfterAd();
  if(typeof ca_api != 'undefined'){
    ca_api.on_ad_error(adErrorEvent);
    ca_api.remove_ad();
  }
};

/**
 * If we aren't playing post-rolls, ALL_ADS_COMPLETED will be fired before
 * the video player fires the ended event. Here ads are done, so if
 * the content video is done, we start the next video. If ads are done but the
 * content video is still playing, we just let it finish.
 * @private
 */
Ads.prototype.onAllAdsCompleted_ = function() {
  this.allAdsCompleted = true;
  if (this.contentCompleteCalled) {
    this.application_.switchButtonToReplay();
  }
  if(typeof ca_api != 'undefined'){
    ca_api.remove_ad();
    ca_api.on_ad_finished();
  }
};
Ads.prototype.onAdLoaded = function() {
  if(typeof ca_api != 'undefined'){
    ca_api.on_ad_start();
  }
};
Ads.prototype.onAdSkipped = function() {
  if(typeof ca_api != 'undefined'){
    ca_api.on_ad_closed();
  }
};

/**
 * Handles user interaction and creates the player and ads controllers.
 */
var Application = function() {
  if(!document.getElementById('ca-ads')){
    var html = '<div id="video-container"><video id="video-element"></video><div id="ad-container"></div></div>';
    var ad_content = document.createElement("div");
    ad_content.setAttribute("id", "ca-ads");
    ad_content.innerHTML = html;
    document.body.appendChild(ad_content);
  }

  this.fullscreenWidth = null;
  this.fullscreenHeight = null;

  document.addEventListener(
        'fullscreenchange', this.bind_(this, this.onFullscreenChange_),
        false);

  this.initialUserActionHappened_ = false;
  this.playing_ = false;
  this.adsActive_ = false;
  this.adsDone_ = false;
  this.fullscreen = false;

  this.videoPlayer_ = new VideoPlayer();
  this.ads_ = new Ads(this, this.videoPlayer_);
  this.adTagUrl_ = '';
  if(typeof ca_api != 'undefined'){
    this.adTagUrl_ = ca_api.ima_adtag;
  }
  this.videoEndedCallback_ = this.bind_(this, this.onContentEnded_);
  this.setVideoEndedCallbackEnabled(true);

  window.addEventListener('resize', this.bind_(this, this.resizeFull), false);
};
Application.prototype.setVideoEndedCallbackEnabled = function(enable) {
  if (enable) {
    this.videoPlayer_.registerVideoEndedCallback(this.videoEndedCallback_);
  } else {
    this.videoPlayer_.removeVideoEndedCallback(this.videoEndedCallback_);
  }
};

Application.prototype.switchButtonToReplay = function() {
  //this.playButton_.style.display = 'none';
  //this.replayButton_.style.display = 'block';
};

Application.prototype.log = function(message) {
  if(typeof ca_api != 'undefined'){
    if(ca_api.debug){
      console.log(message);
    }
  }
};

Application.prototype.resumeAfterAd = function() {
  //this.videoPlayer_.play();
  this.adsActive_ = false;
  this.updateChrome_();
};

Application.prototype.pauseForAd = function() {
  this.adsActive_ = true;
  this.playing_ = true;
  this.videoPlayer_.pause();
  this.updateChrome_();
};

Application.prototype.adClicked = function() {
  this.playing_ = false;
  this.updateChrome_();
};

Application.prototype.bind_ = function(thisObj, fn) {
  return function() {
    fn.apply(thisObj, arguments);
  };
};

Application.prototype.onClick_ = function() {
  if (!this.adsDone_) {
    if (!this.initialUserActionHappened_) {
      // The user clicked/tapped - inform the ads controller that this code
      // is being run in a user action thread.
      this.ads_.initialUserAction();
      this.initialUserActionHappened_ = true;
    }
    // At the same time, initialize the content player as well.
    // When content is loaded, we'll issue the ad request to prevent it
    // from interfering with the initialization. See
    // https://developers.google.com/interactive-media-ads/docs/sdks/html5/v3/ads#iosvideo
    // for more information.
    this.videoPlayer_.preloadContent(this.bind_(this, this.loadAds_));
    this.adsDone_ = true;
    return;
  }

  if (this.adsActive_) {
    if (this.playing_) {
      this.ads_.pause();
    } else {
      this.ads_.resume();
    }
  } else {
    if (this.playing_) {
      this.videoPlayer_.pause();
    } else {
      this.videoPlayer_.play();
    }
  }

  this.playing_ = !this.playing_;

  this.updateChrome_();
};

Application.prototype.onReplay_ = function() {
  this.videoPlayer_.preloadContent(this.bind_(this, this.loadAds_));
  this.adsDone_ = true;
};

Application.prototype.updateChrome_ = function() {
  if (this.playing_) {
    //this.playButton_.textContent = 'II';
  } else {
    // Unicode play symbol.
    //this.playButton_.textContent = String.fromCharCode(9654);
  }
};

Application.prototype.loadAds_ = function() {
  this.videoPlayer_.removePreloadListener();
  this.ads_.requestAds(this.adTagUrl_);
};

Application.prototype.onFullscreenChange_ = function() {
  if (this.fullscreen) {
    // The user just exited fullscreen
    // Resize the ad container
    this.ads_.resize(this.videoPlayer_.width, this.videoPlayer_.height);
    // Return the video to its original size and position
    this.videoPlayer_.resize(
        'relative', '', '', this.videoPlayer_.width, this.videoPlayer_.height);
    this.fullscreen = false;
  } else {
    // The fullscreen button was just clicked
    // Resize the ad container
    var width = this.fullscreenWidth;
    var height = this.fullscreenHeight;
    this.makeAdsFullscreen_();
    // Make the video take up the entire screen
    this.videoPlayer_.resize('absolute', 0, 0, width, height);
    this.fullscreen = true;
  }
};

Application.prototype.makeAdsFullscreen_ = function() {
  this.ads_.resize(this.fullscreenWidth, this.fullscreenHeight);
};

Application.prototype.resizeFull = function() {
  if(typeof this != 'undefined'){
    this.videoPlayer_.width = window.innerWidth;
    this.videoPlayer_.height = window.innerHeight;
    this.ads_.resize(this.videoPlayer_.width, this.videoPlayer_.height);
  }
};

Application.prototype.onContentEnded_ = function() {
  this.ads_.contentEnded();
};

Application.prototype.showAds = function(event) {
  // Terms of Service says we can't kill an ad prematurely, so we will only
  // switch videos if there isn't an ad playing.
  if (!this.ads_.linearAdPlaying) {
    this.ads_.destroyAdsManager();
    this.ads_.contentCompleted();
    if (!this.initialUserActionHappened_) {
      this.ads_.initialUserAction();
      this.initialUserActionHappened_ = true;
    }
    this.adsDone_ = true;
    //this.videoPlayer_.setContentVideoIndex(event.target.id);
    this.videoPlayer_.preloadContent(this.bind_(this, this.loadAds_));
  }
};

/**
 * Handles video player functionality.
 */
var VideoPlayer = function() {
  this.contentPlayer = document.getElementById('video-element');
  this.adContainer = document.getElementById('ad-container');
  this.videoPlayerContainer_ = document.getElementById('video-container');

  this.contentIndex = 0;
  /*this.contentUrls = [
    'https://storage.googleapis.com/gvabox/media/samples/stock.mp4',
    'https://storage.googleapis.com/gvabox/media/samples/android.mp4'
  ];*/

  this.width = window.innerWidth;
  this.height = window.innerHeight;
};

VideoPlayer.prototype.preloadContent = function(contentLoadedAction) {
  // If this is the initial user action on iOS or Android device,
  // simulate playback to enable the video element for later program-triggered
  // playback.
  if (this.isMobilePlatform()) {
    this.preloadListener_ = contentLoadedAction;
    this.contentPlayer.addEventListener(
        'loadedmetadata', contentLoadedAction, false);
    this.setContentVideoSource_(this.contentIndex);
  } else {
    this.setContentVideoSource_(this.contentIndex);
    contentLoadedAction();
  }
};

VideoPlayer.prototype.removePreloadListener = function() {
  if (this.preloadListener_) {
    this.contentPlayer.removeEventListener(
        'loadedmetadata', this.preloadListener_, false);
    this.preloadListener_ = null;
  }
};

VideoPlayer.prototype.play = function() {
  this.contentPlayer.play();
};

VideoPlayer.prototype.pause = function() {
  this.contentPlayer.pause();
};

VideoPlayer.prototype.isMobilePlatform = function() {
  return this.contentPlayer.paused &&
      (navigator.userAgent.match(/(iPod|iPhone|iPad)/) ||
       navigator.userAgent.toLowerCase().indexOf('android') > -1);
};

VideoPlayer.prototype.registerVideoEndedCallback = function(callback) {
  this.contentPlayer.addEventListener('ended', callback, false);
};

VideoPlayer.prototype.removeVideoEndedCallback = function(callback) {
  this.contentPlayer.removeEventListener('ended', callback, false);
};

VideoPlayer.prototype.setContentVideoIndex = function(index) {
  this.contentIndex = index;
};

VideoPlayer.prototype.setContentVideoSource_ = function(index) {
  this.contentIndex = index;
  //this.contentPlayer.src = this.contentUrls[index];
  this.contentPlayer.load();
};