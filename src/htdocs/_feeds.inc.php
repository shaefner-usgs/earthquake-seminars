<?php

include_once '../conf/config.inc.php'; // app config

$feedsHtml = '
<h3 class="podcast">Video Podcast</h3>
<ul class="feeds no-style">
  <li class="itunes">
    <a href="https://podcasts.apple.com/us/podcast/usgs-earthquake-science-center-seminars/id413770595">
      Apple Podcasts
    </a>
  </li>
  <li class="xml">
    <a href="' . $MOUNT_PATH . '/feed/">
      RSS Feed
    </a>
  </li>
</ul>
';
