'use strict';


var VideoPlayer = require('VideoPlayer');

// convert video tag to jwplayer instance to maximize compatibility
var video = document.querySelector('video');

if (video) {
  VideoPlayer({
    el: video
  });
}
