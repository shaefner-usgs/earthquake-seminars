<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

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

$prevMonth = NULL;
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
    $openTag = '<a href="seminars/' . $row->ID . '">';
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

<?php print $seminarsHtml; ?>
