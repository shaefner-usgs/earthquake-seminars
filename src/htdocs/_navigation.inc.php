<?php

include_once '../conf/config.inc.php'; // app config

$section = $MOUNT_PATH;
$url = $_SERVER['REQUEST_URI'];

// Set up page match for index page
$matches = false;
if (preg_match("@^$section/?$@", $url)) {
  $matches = true;
}

// Concatenate navItems for each year of past seminars
$navItems = '';
$beginYear = 2000;
$currentYear = date('Y');
for ($year = $currentYear; $year >= $beginYear; $year --) {
  $navItems .= navItem("$section/$year", $year);
}

// Only expand navGroup if viewing past seminars
if (preg_match("@^$section/\d{4}$@", $url)) {
  $pastNav = navGroup('Past', $navItems);
} else {
  $pastNav = navItem("$section/$currentYear", 'Past');
}

$NAVIGATION =
  navGroup('Seminars',
    navItem("$section", 'Upcoming', $matches) .
    $pastNav .
    navItem("$section/committee.php", 'Seminar Committee') .
    navItem("http://online.wr.usgs.gov/kiosk/mparea3.html",
      'Campus Map and Directions')
  );

print $NAVIGATION;
