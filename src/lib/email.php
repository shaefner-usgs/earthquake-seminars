#!/usr/bin/php
<?php

/**
 * PURPOSE: script sends out email announcements of upcoming seminars
 *   called by crontab (shaefner) every 15 minutes
 */

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$committee = '';

$db = new Db;

// 2.5 hour announcement
$datetime = strftime('%Y-%m-%d %H:%M:00', strtotime('+150 minutes'));
$rsSeminars = $db->querySeminars($datetime);
prepare($rsSeminars);

// 2 day announcement
$datetime = strftime('%Y-%m-%d %H:%M:00', strtotime('+2 days'));
$rsSeminars = $db->querySeminars($datetime);
prepare($rsSeminars);


/**
 * Create email message
 *
 * @param $recordSet {Recordset}
 *
 * @return {Array}
 */
function createEmail ($recordSet) {
  global $committee;
  $row = $recordSet->fetch();

  // Assume -no seminar- if speaker is empty (committee likes to post no seminar msg on web page)
  if (!$row['speaker'] || ($row['publish'] === 'no')) {
    return;
  }

  if (!$committee) {
    $committee = getCommittee();
  }

  $affiliation = $row['affiliation'];
  $date = date('l, F j', strtotime($row['datetime']));
  $location = $row['location'];
  $speaker = $row['speaker'];
  $time = date('g:i A', strtotime($row['datetime']));
  $topic = $row['topic'];

  $summary = '';
  if ($row['summary']) {
    $summary = "\n" . $row['summary'];
  }

  if ($row['video'] === 'yes') {
    $id = $row['ID'];
    $video_msg = "Webcast (live and archive):\nhttps://earthquake.usgs.gov/contactus/menlo/seminars/$id";
  } else {
    $video_msg = 'This seminar will not be webcast.';
  }

  // Create email message
  $message = "Earthquake Science Center Seminars

Who:
$speaker, $affiliation

What:
$topic$summary

When:
$date at $time

Where:
$location

$video_msg

-------------------------------------------------------------------------------

Please contact the Seminar co-Chairs for speaker suggestions or if you would
like to meet with the speaker:

{$committee['list']}";

  return [
    'datetime' => $row['datetime'],
    'message' => $message,
    'speaker' => $speaker
  ];
}

/**
 * Get committee members
 *
 * @return $r {Array}
 */
function getCommittee () {
  global $db;

  $firstPass = true;
  $r = [
    'list' => ''
  ];
  $rsCommittee = $db->queryCommittee();

  while ($row = $rsCommittee->fetch(PDO::FETCH_ASSOC)) {
    if ($firstPass) {
      // Store 1st committee member as POC for email announcement
      $r['poc'] = $row;
    }
    $r['list'] .= sprintf (" * %s (%s), %s\r\n",
      $row['name'],
      $row['email'],
      $row['phone']
    );
    $firstPass = false;
  }

  return $r;
}

/**
 * Call methods to create email and then send it
 *
 * @param $recordSet {Recordset}
 *
 */
function prepare($recordSet) {
  if ($recordSet->rowCount() > 0) {
    $email = createEmail($recordSet);
    if ($email) {
      sendEmail($email);
    }
  }
}

/**
 * Send email
 *
 * @param $seminar {Array}
 */
function sendEmail ($seminar) {
  global $committee;

  $seminarDay = date('l', strtotime($seminar['datetime']));
  $today = date('l');
  if ($seminarDay === $today) {
    $when = "today at $time";
  }
  else {
    $when = "this $seminarDay";
  }

  $headers = sprintf("From: %s<%s>\r\n",
    $committee['poc']['name'],
    $committee['poc']['email']
  );
  $to = 'GS-G-WR_EHZ_Seminars@usgs.gov';
  $subject = 'Earthquake Seminar ' . $when . ' - ' . $seminar['speaker'];

  mail ($to, $subject, $seminar['message'], $headers);
}
