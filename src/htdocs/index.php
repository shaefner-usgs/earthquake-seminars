<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '_feeds.inc.php'; // sets $feeds

include_once '../lib/classes/Seminar.class.php'; // model
include_once '../lib/classes/SeminarListView.class.php'; // view
include_once '../lib/classes/SeminarCollection.class.php'; // collection

$year = safeParam('year'); // gets set if user viewing archives

if (!isset($TEMPLATE)) {
  $TITLE = 'Earthquake Science Center Seminars';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="'. $MOUNT_PATH . '/css/index.css" />';
  $FOOT = '';

  // Don't cache
  $expires = date(DATE_RFC2822);
  header('Cache-control: no-cache, must-revalidate');
  header("Expires: $expires");

  include 'template.inc.php';
}

$db = new Db();
$seminarCollection = new seminarCollection();

// Db query result: seminars in a given year, or future seminars if $year=NULL
$rsSeminars = $db->querySeminars($year);

// Create seminar collection
$rsSeminars->setFetchMode(PDO::FETCH_CLASS, Seminar);
$seminars = $rsSeminars->fetchAll();
foreach($seminars as $seminar) {
  $seminarCollection->add($seminar);
}

$view = new SeminarListView($seminarCollection);

if (!$year) { ?>

<p>Seminars typically take place at <strong>10:30 AM</strong> on <strong>Wednesdays</strong>
  in the <strong>Rambo Auditorium</strong> (main USGS Conference Room). The USGS
  Campus is located at <a href="/contactus/menlo/menloloc.php" title="Campus
  Map and Directions">345 Middlefield Road, Menlo Park, CA</a>.</p>

<p>We record most seminars. You can watch live or
  <a href="<?php print $MOUNT_PATH; ?>/archives/<?php print $currentYear; ?>">check
  the archives</a> to view a past seminar.</p>

<?php }

$view->render();

print $feeds;
