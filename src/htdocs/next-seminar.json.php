<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/classes/SeminarCollection.class.php'; // collection

// Don't cache
$now = date(DATE_RFC2822);
header("Expires: $now");
header('Content-Type: application/json');

$seminarCollection = new seminarCollection();
$seminarCollection->addUpcoming();

$nextSeminar = [
  'metadata' => [
    'requested' => $now
  ],
  'seminar' => 'No upcoming seminars' // default
];

foreach($seminarCollection->seminars as $seminar) {
  if ($seminar->speaker) { // assume "no seminar" entry if speaker is NULL
    $nextSeminar['seminar'] = [
      'timestamp' => $seminar->timestamp,
      'speaker' => $seminar->speakerWithAffiliation,
      'title' => $seminar->topic
    ];

    break;
  }
}

$json = json_encode($nextSeminar);
print $json;
