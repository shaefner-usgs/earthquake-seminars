<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/classes/Db.class.php'; // db connector, queries

// Don't cache
$now = date(DATE_RFC2822);
header("Expires: $now");
header('Content-Type: application/json');

$db = new Db();

$rsSeminars = $db->querySeminars();

// Create array for seminar data
$nextSeminar = [
  'metadata' => [
    'requested' => $now
  ]
];

$row = $rsSeminars->fetch(PDO::FETCH_OBJ);

$speaker = $row->speaker;
if ($row->affiliation) {
  $speaker .= ', ' . $row->affiliation;
}

$nextSeminar['seminar'] = [
  'timestamp' => strtotime($row->datetime),
  'speaker' => $speaker,
  'topic' => $row->topic
];


$json = json_encode($nextSeminar);
print $json;

?>
