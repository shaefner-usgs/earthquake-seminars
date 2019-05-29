<?php

include_once '../../../conf/config.inc.php'; // app config

if (!isset($TEMPLATE)) {
  $TITLE = 'Internal Live Webcast';
  $NAVIGATION = true;
  $HEAD = '';
  $FOOT = '
    <script src="../lib/jwplayer/jwplayer.js"></script>
    <script src="../js/seminar.js"></script>
  ';

  include 'template.inc.php';
}

?>

<div class="row live">
  <div class="column two-of-three video">
    <video src="internal?streamer=rtmp://video2.wr.usgs.gov/live"
      width="100%" controls="controls">
    </video>
    <p><a href="http://video2.wr.usgs.gov:1935/live/internal/playlist.m3u8">View
      on a mobile device</a></p>
  </div>
  <div class="column one-of-three">
    <p style="margin-top: 0;">We usually start broadcasting live webcasts at
      least 5 minutes before the start of the lecture or presentation.</p>
    <p class="flash"><a href="http://get.adobe.com/flashplayer/">Adobe Flash
      Player</a> is <strong>required</strong> to view live webcasts.</p>
  </div>
</div>
