'use strict';


var VideoPlayer = require('VideoPlayer');

// convert video tag to jwplayer instance to maximize compatibility
(function () {
  var options,
      playlist,
      video;

  playlist = document.querySelector('.playlist');
  video = document.querySelector('video');

  options = {
    el: video
  };
  if (playlist) {
    options.elPlaylist = playlist;
  }

  if (video) {
    VideoPlayer(options);
  }
})();
