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
    navItem("$section/", 'Upcoming', $matchesUpcoming) .
    $pastNav .
    navItem("$section/committee.php", 'Committee') //.
    //navItem("$section/email-list.php", 'Email List')
  );

$NAVIGATION .=
  navGroup('Related Seminars',
    navItem('https://www.usgs.gov/science-support/communications-and-publishing/public-lecture-series', 'USGS Public Lecture Series') .
    navItem('https://www.usgs.gov/centers/pcmsc/science/science-seminar-series?qt-science_center_objects=0#qt-science_center_objects', 'Pacific Coastal and Marine Science Center') .
    navItem('https://earth.stanford.edu/geophysics/events', 'Stanford Geophysics')
  );

print $NAVIGATION;
