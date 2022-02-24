<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/SeminarListView.class.php'; // view
include_once '../lib/classes/SeminarCollection.class.php'; // collection
include_once '_feeds.inc.php'; // sets $feedsHtml

$year = safeParam('year'); // gets set if user viewing archives

if (!isset($TEMPLATE)) {
  $TITLE = 'Earthquake Science Center Seminars';
  if ($year) {
    $TITLETAG = "$year Archives | $TITLE";
  }
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="'. $MOUNT_PATH . '/css/index.css" />';
  $FOOT = '';

  // Don't cache
  $expires = date(DATE_RFC2822);
  header('Cache-control: no-cache, must-revalidate');
  header("Expires: $expires");

  include 'template.inc.php';
}

$seminarCollection = new seminarCollection();

if ($year) {
  $seminarCollection->addYear($year);
} else {
  $seminarCollection->addUpcoming();
}

$view = new SeminarListView($seminarCollection);
$view->render($year);

print $feedsHtml;
