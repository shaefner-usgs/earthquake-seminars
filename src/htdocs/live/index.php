<?php

	$TITLE = "Live Webcast";
	$CONTACT = "shaefner";
  $TEMPLATE = "onecolumn";
  $WIDGETS = 'jwplayer';
  $SHARE = false;

  include $_SERVER['DOCUMENT_ROOT'] . "/template/template.inc.php";

?>

<div id="live" class="nine column">
  <video src="mplive?streamer=rtmp://video2.wr.usgs.gov/live" width="704" height="396" controls></video>
</div>

<div class="three column">
  <p>We usually start broadcasting the live stream at least 5 minutes before the start of the lecture or presentation.</p>
  <p>Requires the <a href="http://get.adobe.com/flashplayer/">Adobe Flash Player</a> plug-in.</p>
  <p><a href="http://video2.wr.usgs.gov:1935/live/mplive/playlist.m3u8">View on a mobile device</a></p>
</div>