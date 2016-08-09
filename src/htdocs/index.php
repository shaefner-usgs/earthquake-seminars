<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$currentYear = date('Y');
$year = safeParam('year');

if (!isset($TEMPLATE)) {
  $TITLE = 'Earthquake Science Center Seminars';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="'. $MOUNT_PATH . '/css/index.css" />';
  $FOOT = '';

  include 'template.inc.php';
}

$db = new Db();

// $year is NULL when viewing default page (upcoming seminars)
$rsSeminars = $db->querySeminars($year);

$cssClass = ' upcoming';
if ($year) {
  $cssClass = ' archives';
}

// Create HTML for seminars list
$prevMonth = NULL;
$seminarsHtml = '';
while ($row = $rsSeminars->fetch(PDO::FETCH_OBJ)) {
  $affiliation = NULL;
  $timestamp = strtotime($row->datetime);

  $seminarDate = date('D, M j', $timestamp);
  $seminarDay = date('D', $timestamp);
  $seminarMonth = date('F', $timestamp);
  $seminarTime = date('g:i A', $timestamp);
  $seminarYear = date('Y', $timestamp);

  // Flag upcoming seminars that aren't on the "regular" day/time
  if ($seminarDay !== 'Wed' && !$year) {
    $seminarDate = "<mark>$seminarDate</mark>";
  }
  if ($seminarTime !== '10:30 AM' && !$year) {
    $seminarTime = "<mark>$seminarTime</mark>";
  }

  // Show month & year header; open/close <ul>'s
  if ($seminarMonth !== $prevMonth) {
    if ($prevMonth) {
      $seminarsHtml .= '</ul>';
    }
    $seminarsHtml .= "<h2>$seminarMonth $seminarYear</h2>";
    $seminarsHtml .= '<ul class="seminars no-style' . $cssClass . '">';
  }
  if ($row->affiliation) {
    $affiliation = ', ' . $row->affiliation;
  }

  // speaker field empty if no seminar (committee posts "no seminar" messages
  if ($row->speaker) {
    $openTag = '<a href="seminars/id' . $row->ID . '">';
    $closeTag = '</a>';
  } else {
    $openTag = '<div>';
    $closeTag = '</div>';
  }

  $seminarsHtml .= sprintf('<li>
      %s
        <div class="topic">
          <h3>%s</h3>
          <p>%s%s</p>
        </div>
        <time datetime="%s">
          %s <span class="time">%s</span>
        </time>
      %s
    </li>',
    $openTag,
    $row->topic,
    $row->speaker,
    $affiliation,
    date('c', $timestamp),
    $seminarDate,
    $seminarTime,
    $closeTag
  );

  $prevMonth = $seminarMonth;
}
$seminarsHtml .= '</ul>';

?>

<p>Seminars typically take place at <strong>10:30 AM Wednesdays</strong> in the
  <strong>Rambo Auditorium</strong> (main USGS Conference Room). The USGS Campus
  is located at <a href="/contactus/menlo/menloloc.php" title="Campus Map and Directions">345
  Middlefield Road, Menlo Park, CA</a>.</p>

<p>We record most seminars. You can watch live or
  <a href="seminars/<?php print $currentYear; ?>">check the archives</a> to
  view a past seminar.</p>

<h3>Video Podcast</h3>

<ul class="feeds no-style">
  <li class="itunes">
    <a href="http://itunes.apple.com/us/podcast/usgs-earthquake-science-center/id413770595">
      iTunes
    </a>
  </li>
  <li class="xml">
    <a href="<?php print $MOUNT_PATH; ?>/podcast.xml">
      XML (Atom)
    </a>
  </li>
</ul>

<?php print $seminarsHtml; ?>
