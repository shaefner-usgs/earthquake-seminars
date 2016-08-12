'use strict';


var Video = require('Video');

// convert video tag to jwplayer instance to maximize compatibility
Video({
  el: document.querySelector('video')
});
