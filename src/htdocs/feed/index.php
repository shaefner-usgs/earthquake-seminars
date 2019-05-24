<?php

include_once '../../conf/config.inc.php'; // app config
include_once '../../lib/classes/Db.class.php'; // db connector, queries

include_once '../../lib/classes/Seminar.class.php'; // model
include_once '../../lib/classes/SeminarCollection.class.php'; // collection
include_once "../../lib/classes/Feed.class.php"; // creates feed

$db = new Db();
$seminarCollection = new seminarCollection();

// Db query result: past 12 seminars with a video
$rsSeminars = $db->queryPodcastVideos();

// Create seminar collection
$rsSeminars->setFetchMode(PDO::FETCH_CLASS, Seminar);
$seminars = $rsSeminars->fetchAll();
foreach($seminars as $seminar) {
  $seminarCollection->add($seminar);
}

header('Content-Type: application/xml');

$feed = new Feed([
  'baseUri' => 'https://earthquake.usgs.gov/contactus/menlo/seminars',
  'collection' => $seminarCollection,
  'template' => "$APP_DIR/htdocs/feed/template.xml"
]);
$feed->render();
