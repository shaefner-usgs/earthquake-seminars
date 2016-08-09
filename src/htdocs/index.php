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

$prevMonth = NULL;
while ($row = $rsSeminars->fetch(PDO::FETCH_OBJ)) {
  $affiliation = NULL;
  $timestamp = strtotime($row->datetime);
  $seminarMonth = date('F', $timestamp);
  $seminarYear = date('Y', $timestamp);

  if ($seminarMonth !== $prevMonth) {
    if ($prevMonth) {
      $seminarsHtml .= '</ul>';
    }
    $seminarsHtml .= "<h2>$seminarMonth $seminarYear</h2>";
    $seminarsHtml .= '<ul class="seminars no-style">';
  }
  if ($row->affiliation) {
    $affiliation = ', ' . $row->affiliation;
  }

  $seminarsHtml .= sprintf('<li>
      <a href="seminars/%s">
        <div class="topic">
          <h3>%s</h3>
          <p>%s%s</p>
        </div>
        <time datetime="%s">
          %s <span>%s</span>
        </time>
      </a>
    </li>',
    $row->ID,
    $row->topic,
    $row->speaker,
    $affiliation,
    date('c', $timestamp),
    date('D, M j', $timestamp),
    date('h:i A', $timestamp)
  );

  $prevMonth = $seminarMonth;
}
$seminarsHtml .= '</ul>';

?>

<?php print $seminarsHtml; ?>
