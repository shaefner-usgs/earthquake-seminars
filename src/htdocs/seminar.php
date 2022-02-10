<?php

include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Seminar.class.php'; // model
include_once '../lib/classes/SeminarView.class.php'; // view
include_once '../lib/classes/SeminarCollection.class.php'; // collection
include_once '_feeds.inc.php'; // sets $feedsHtml

$id = safeParam('id', '1056');
$seminarCollection = new seminarCollection();
$seminarCollection->addSeminarWithId($id);

if ($seminarCollection->seminars) {
  $seminar = $seminarCollection->seminars[0];
} else {
  $seminar = new Seminar();
  $seminar->topic = 'Seminar not found';
}

if (!isset($TEMPLATE)) {
  $TITLE = 'Earthquake Science Center Seminars';
  $TITLETAG = "$seminar->topic | Earthquake Science Center Seminars";
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="lib/jwplayer/skins/five.css" />
    <link rel="stylesheet" href="css/seminar.css" />';
  $FOOT = '
    <script src="lib/jwplayer/jwplayer.js"></script>
    <script src="js/index.js"></script>
  ';

  // Don't cache
  $expires = date(DATE_RFC2822);
  header('Cache-control: no-cache, must-revalidate');
  header("Expires: $expires");

  include 'template.inc.php';
}

$view = new SeminarView($seminar);
$view->render();

print $feedsHtml;
