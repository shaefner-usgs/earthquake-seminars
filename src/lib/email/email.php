#!/usr/bin/php
<?php

/**
 * PURPOSE: script sends out email announcements of upcoming seminars
 *   called by crontab (esc user) every 15 minutes
 */

$cwd = dirname(__FILE__);

include_once "$cwd/../conf/config.inc.php"; // app config
include_once "$cwd/../lib/classes/Db.class.php"; // db connector, queries

$db = new Db;

$committee = '';

// 2.5 hour announcement
$datetime = strftime('%Y-%m-%d %H:%M:00', strtotime('+150 minutes'));
$rsSeminars = $db->querySeminars($datetime);
prepare($rsSeminars);

// 2 day announcement
$datetime = strftime('%Y-%m-%d %H:%M:00', strtotime('+2 days'));
$rsSeminars = $db->querySeminars($datetime);
prepare($rsSeminars);

// 7 day announcement (sends to NASA only)
$datetime = strftime('%Y-%m-%d %H:%M:00', strtotime('+7 days'));
$rsSeminars = $db->querySeminars($datetime);
prepare($rsSeminars, $NASA_EMAIL);

// $test = '2017-03-22 10:30:00';
// $rsSeminars = $db->querySeminars($test);
// prepare($rsSeminars, 'shaefner@usgs.gov');

/**
 * Create email message
 *
 * @param $recordSet {Recordset}
 * @param $to {String} email address
 *
 * @return {Array}
 */
function createEmail ($recordSet, $to) {
  global $committee;

  $row = $recordSet->fetch();

  // Assume -no seminar- if speaker is empty (committee likes to post no seminar msg on web page)
  if (!$row['speaker'] || ($row['publish'] === 'no')) {
    return;
  }

  if (!$committee) {
    $committee = getCommittee();
    $committeeList = $committee['list'];
  }

  $timestamp = strtotime($row['datetime']);

  $affiliation = $row['affiliation'];
  $date = date('l, F j', $timestamp);
  $location = $row['location'];
  $speaker = $row['speaker'];
  $time = date('g:i A', $timestamp);
  $topic = $row['topic'];

  $summary = '';
  if ($row['summary']) {
    $summary = "\n\n" . $row['summary'];
  }

  // Set video blurb
  if ($row['video'] === 'yes') {
    $id = $row['ID'];
    $videoMsg = "Webcast (live and archive):\nhttps://earthquake.usgs.gov/contactus/menlo/seminars/$id";
  } else {
    $videoMsg = 'This seminar will not be webcast.';
  }

  // Set relative time
  $today = date('l, F j');
  if ($date === $today) {
    $when = "today at $time";
  }
  else {
    $dayOfWeek = date('l', $timestamp);
    $sixDays = 60 * 60 * 24 * 6;
    $timestampNow = time();
    if (($timestamp - $timestampNow) >= $sixDays) {
      $when = "next $dayOfWeek";
    } else {
      $when = "this $dayOfWeek";
    }
  }

  $subject = 'Earthquake Seminar ' . $when . ' - ' . $speaker;

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

$videoMsg

-------------------------------------------------------------------------------

Please contact the Seminar co-Chairs for speaker suggestions or if you would
like to meet with the speaker:

$committeeList";

  return [
    'message' => $message,
    'subject' => $subject,
    'to' => $to
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
 * @param $to {String} email address
 *     optional parameter to set an alernate email address for announcement
 */
function prepare($recordSet, $to=NULL) {
  if ($recordSet->rowCount() > 0) {
    // If '$to' not set, use default USGS distribution list
    if (!$to) {
      $to = $GLOBALS['USGS_EMAIL'];
    }
    $email = createEmail($recordSet, $to);
    if ($email) {
      sendEmail($email);
    }
  }
}

/**
 * Send email
 *
 * @param $email {Array}
 */
function sendEmail ($email) {
  global $committee;

  $headers = sprintf("From: %s<%s>\r\n",
    $committee['poc']['name'],
    $committee['poc']['email']
  );

  mail($email['to'], $email['subject'], $email['message'], $headers);
}
