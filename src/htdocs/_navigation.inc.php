<?php

include_once '../conf/config.inc.php'; // app config

$section = $MOUNT_PATH;
$url = $_SERVER['REQUEST_URI'];

// Set up page match for index page
$matches = false;
if (preg_match("@^$section/?$@", $url)) {
  $matches = true;
}

// Create navGroup for archived seminars organized by year
$archives = '';
$beginYear = 2000;
$endYear = date('Y');
for ($year = $endYear; $year >= $beginYear; $year --) {
  $archives .= navItem("$section/$year", $year);
}

$NAVIGATION =
  navGroup('Seminars',
    navItem("$section", 'Upcoming', $matches) .
    navGroup('Past', $archives) .
    navItem("$section/committees.php", 'Seminar Committee') .
    navItem("http://online.wr.usgs.gov/kiosk/mparea3.html",
      'Campus Map and Directions')
  );

print $NAVIGATION;
