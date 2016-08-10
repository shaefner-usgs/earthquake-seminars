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
  $navItems .= navItem("$section/archives/$year", $year);
}

// Only expand navGroup if viewing past seminars
if (preg_match("@^$section/archives/\d{4}$@", $url)) {
  $pastNav = navGroup('Archives', $navItems);
} else {
  $pastNav = navItem("$section/archives/$currentYear", 'Archives');
}

$NAVIGATION =
  navGroup('Earthquake Seminars',
    navItem("$section", 'Upcoming', $matches) .
    $pastNav .
    navItem("$section/committee.php", 'Committee')
  );

$NAVIGATION .=
  navGroup('Related Seminars',
    navItem('http://online.wr.usgs.gov/calendar/', 'USGS Evening Public Lecture') .
    navItem('http://wwwrcamnl.wr.usgs.gov/prc/', 'USGS Western Region Colloquium') .
    navItem('http://volcanoes.usgs.gov/seminar.html', 'USGS Volcano Hazards') .
    navItem('http://wwwrcamnl.wr.usgs.gov/wrdseminar/', 'USGS Water Resources') .
    navItem('https://earth.stanford.edu/geophysics/events', 'Stanford Geophysics')
  );

print $NAVIGATION;
