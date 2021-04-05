<?php

$section = $MOUNT_PATH;
$url = strtok($_SERVER['REQUEST_URI'], '?'); // strip querystring

// Set up page match for highlighting selected item in navbar
$matchesArchives = false;
$matchesUpcoming = false;
if (preg_match('/future|live|today/', $seminar->status) || preg_match("@^$section/?$@", $url)) {
  $matchesUpcoming = true;
} else if ($seminar->status === 'past' || preg_match('/archives/', $url)) {
  $matchesArchives = true;
}

// Create navItems for each year of seminar archives
$navItems = '';
$beginYear = 2000;
$currentYear = date('Y');
for ($year = $currentYear; $year >= $beginYear; $year --) {
  $navItems .= navItem("$section/archives/$year", $year);
}

// Only expand navGroup if viewing archives
if (preg_match("@^$section/archives/\d{4}$@", $url)) {
  $pastNav = navGroup('Archives', $navItems);
} else {
  $pastNav = navItem("$section/archives/$currentYear", 'Archives', $matchesArchives);
}

$NAVIGATION =
  navGroup('Earthquake Seminars',
    navItem("$section", 'Upcoming', $matchesUpcoming) .
    $pastNav .
    navItem("$section/committee.php", 'Committee') //.
    //navItem("$section/email-list.php", 'Email List')
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
