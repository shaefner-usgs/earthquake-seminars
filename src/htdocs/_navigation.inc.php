<?php

include_once '../conf/config.inc.php'; // app config

$section = $MOUNT_PATH;
$url = $_SERVER['REQUEST_URI'];

$matches = false;
if (preg_match("@^$section/\d{4}@", $url)) {
  $matches = true;
}

$archives = '';
$beginYear = 2000;
$endYear = date('Y');
for ($year = $endYear; $year >= $beginYear; $year --) {
  $archives .= navItem("$section/$year", $year);
}

$NAVIGATION =
  navGroup('Seminars',
    navItem("$section", 'Upcoming') .
    navGroup('Past', $archives) .
    navItem("$section/committees.php", 'Past Committees') .
    navItem("http://online.wr.usgs.gov/kiosk/mparea3.html",
      'Campus Map and Directions')
  );

print $NAVIGATION;
