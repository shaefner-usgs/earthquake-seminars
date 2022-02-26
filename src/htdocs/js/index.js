'use strict';


var VideoPlayer = require('VideoPlayer');


// Convert "standard" <video> to a jwplayer instance
(() => {
  var video = document.querySelector('video');

  if (video) {
    VideoPlayer({
      el: video,
      elPlaylist: document.querySelector('.playlist')
    });
  }
})();
