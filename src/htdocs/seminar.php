<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '_feeds.inc.php'; // sets $feedHtml

include_once '../lib/classes/Seminar.class.php'; // model
include_once '../lib/classes/SeminarView.class.php'; // view

$id = safeParam('id', '1056');

if (!isset($TEMPLATE)) {
  $TITLE = 'Earthquake Science Center Seminars';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="lib/jwplayer/skins/five.css" />
    <link rel="stylesheet" href="css/seminar.css" />';
  $FOOT = '
    <script src="lib/jwplayer/jwplayer.js"></script>
    <script src="js/seminar.js"></script>
  ';

  // Don't cache
  $expires = date(DATE_RFC2822);
  header('Cache-control: no-cache, must-revalidate');
  header("Expires: $expires");

  include 'template.inc.php';
}

$db = new Db();
$seminarModel = new Seminar();

// Db query result: details for selected seminar
$rsSeminar = $db->querySeminar($id);

// Create seminar model from selected seminar
if ($rsSeminar->rowCount() === 1) {
  $rsSeminar->setFetchMode(PDO::FETCH_CLASS, Seminar);
  $seminarModel = $rsSeminar->fetch();
}

$view = new SeminarView($seminarModel);
$view->render();

print $feedHtml;
