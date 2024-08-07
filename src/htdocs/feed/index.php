<?php

include_once '../../conf/config.inc.php'; // app config
include_once "../../lib/classes/Feed.class.php"; // creates feed
include_once '../../lib/classes/SeminarCollection.class.php'; // collection

$seminarCollection = new seminarCollection();
$seminarCollection->addRecent();

$feed = new Feed([
  'seminars' => $seminarCollection->seminars,
  'template' => "$APP_DIR/htdocs/feed/template.xml"
]);

header('Content-Type: application/xml; charset=utf-8');
$feed->render();
