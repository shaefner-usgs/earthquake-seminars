<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Seminar.class.php'; // model
include_once '../lib/classes/SeminarListView.class.php'; // view
include_once '../lib/classes/SeminarCollection.class.php'; // collection

$year = safeParam('year');

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

$seminars = $rsSeminars->fetchAll(PDO::FETCH_CLASS, Seminar);
foreach($seminars as $seminar) {
  $seminarCollection->add($seminar);
}

$view = new SeminarListView($seminarCollection, $year);
$view->render();

?>
