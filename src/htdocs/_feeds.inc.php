<?php

include_once '../conf/config.inc.php'; // app config

$feedHtml = '
<h3 class="podcast">Video Podcast</h3>
<ul class="feeds no-style">
  <li class="itunes">
    <a href="http://itunes.apple.com/us/podcast/usgs-earthquake-science-center/id413770595">
      iTunes
    </a>
  </li>
  <li class="xml">
    <a href="' . $MOUNT_PATH . '/feed/">
      RSS Feed
    </a>
  </li>
</ul>
';
