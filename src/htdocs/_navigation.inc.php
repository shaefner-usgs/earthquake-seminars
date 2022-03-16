<?php

$beginYear = 2000;
$currentYear = date('Y');
$isArchives = false;
$isUpcoming = false;
$navItems = '';
$section = $MOUNT_PATH;
$url = strtok($_SERVER['REQUEST_URI'], '?'); // strip querystring

if (!isSet($seminar)) {
  $seminar = new stdClass;
  $seminar->status = '';
}

// Boolean values used to highlight the appropriate section on the navbar
if (preg_match("@^$section/?$@", $url) || preg_match('/future|live|today/', $seminar->status)) {
  $isUpcoming = true;
} else if (preg_match('/archives/', $url) || $seminar->status === 'past') {
  $isArchives = true;
}

// Create $navItems for each year of seminar archives
for ($year = $currentYear; $year >= $beginYear; $year --) {
  $navItems .= navItem("$section/archives/$year", $year);
}

// Expand to show all years when user is viewing archives section
if (preg_match("@^$section/archives/\d{4}$@", $url)) {
  $pastNav = navGroup('Archives', $navItems);
} else {
  $pastNav = navItem("$section/archives/$currentYear", 'Archives', $isArchives);
}

$NAVIGATION =
  navGroup('Earthquake Seminars',
    navItem("$section/", 'Upcoming', $isUpcoming) .
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
