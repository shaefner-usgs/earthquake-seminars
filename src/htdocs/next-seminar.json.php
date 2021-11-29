<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/classes/Db.class.php'; // db connector, queries
include_once '../lib/classes/Seminar.class.php'; // model

// Don't cache
$now = date(DATE_RFC2822);
header("Expires: $now");
header('Content-Type: application/json');

$db = new Db();

$rsSeminars = $db->querySeminars();
$rsSeminars->setFetchMode(PDO::FETCH_CLASS, 'Seminar');
$seminars = $rsSeminars->fetchAll();

// Create array for seminar data
$nextSeminar = [
  'metadata' => [
    'requested' => $now
  ],
  'seminar' => 'No upcoming seminars' // default
];

foreach($seminars as $seminar) {
  $speaker = $seminar->speaker;

  if ($speaker) { // assume "no seminar" record if speaker is NULL
    if ($seminar->affiliation) {
      $speaker .= ', ' . $seminar->affiliation;
    }

    $nextSeminar['seminar'] = [
      'timestamp' => strtotime($seminar->datetime),
      'speaker' => $speaker,
      'title' => $seminar->topic
    ];

    break;
  }
}

$json = json_encode($nextSeminar);
print $json;

?>
