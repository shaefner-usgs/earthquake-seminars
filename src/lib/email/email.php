#!/usr/bin/php
<?php

/**
 * PURPOSE: script sends out email announcements of upcoming seminars
 *   called by crontab (esc user) every 15 minutes
 */

$cwd = dirname(__FILE__);

include_once "$cwd/../../conf/config.inc.php"; // app config
include_once "$cwd/../_autop.inc.php"; // utility function that creates <p>s and <br>s
include_once "$cwd/../classes/Db.class.php"; // db connector, queries
include_once "$cwd/../classes/Email.class.php"; // creates, sends email

$db = new Db;

// 2.5 hour announcement
prepare('+150 minutes', $USGS_EMAIL);

// 2 day announcement
prepare('+2 days', $USGS_EMAIL);

// 7 day announcement (sends to NASA only)
prepare('+7 days', $NASA_EMAIL);

// Test announcement
// prepare('2019-05-22 10:30:00', 'shaefner@usgs.gov');


/**
 * Get seminar committee
 *
 * @return {Array}
 */
function getCommittee () {
  global $db;

  $committee = [];
  $rsCommittee = $db->queryCommittee();

  while ($coChair = $rsCommittee->fetch(PDO::FETCH_OBJ)) {
    $committee[] = [
      'email' => $coChair->email,
      'name' => $coChair->name,
      'phone' => $coChair->phone
    ];
  }

  return $committee;
}

/**
 * Get key-value pairs used to populate mustache template with seminar details
 *
 * @param $seminar {Object}
 * @param $committee {Array}
 *
 * @return {Array}
 */
function getData ($seminar, $committee) {
  $timestamp = strtotime($seminar->datetime);
  $date = getDateStr($timestamp);
  $time = getTimeStr($timestamp);

  $buttonText = 'Watch Online';
  $videoText = 'Watch live or view the recorded talk after it&rsquo;s archived (usually within a couple hours).';
  if ($seminar->video === 'no') {
    $buttonText = 'View Seminar';
    $videoText = 'This seminar will not be live-streamed.';
  }

  return [
    'button-text' => $buttonText,
    'current-year' => date('Y'),
    'date-time' =>  "$date at $time",
    'email1' => $committee[0]['email'],
    'email2' => $committee[1]['email'],
    'host' => $seminar->host,
    'id' => $seminar->ID,
    'location' => $seminar->location,
    'name1' => $committee[0]['name'],
    'name2' => $committee[1]['name'],
    'phone1' => $committee[0]['phone'],
    'phone2' => $committee[1]['phone'],
    'speaker' => getSpeaker($seminar),
    'summary' => getSummary($seminar),
    'time' => $time,
    'title' => $seminar->topic,
    'video-text' => $videoText
  ];
}

/**
 * Get formatted date string (e.g. 'Wednesday, January 1')
 *
 * @param $timestamp {Unix timestamp}
 *
 * @return {String}
 */
function getDateStr ($timestamp) {
  return date('l, F j', $timestamp);
}

/**
 * Get speaker name including affiliation, if available
 *
 * @param $seminar {Object}
 *
 * @return $speaker {String}
 */
function getSpeaker ($seminar) {
  $speaker = $seminar->speaker;
  if ($seminar->affiliation) {
    $speaker .= ', ' . $seminar->affiliation;
  }

  return $speaker;
}

/**
 * Get email subject
 *
 * @param $seminar {Object}
 *
 * @return {String}
 */
function getSubject ($seminar) {
  $timestamp = strtotime($seminar->datetime);
  $seminarDate = getDateStr($timestamp);
  $seminarTime = getTimeStr($timestamp);

  $timestampNow = time();
  $todaysDate = getDateStr($timestampNow);

  // Get relative time
  if ($seminarDate === $todaysDate) {
    $when = "today at $seminarTime";
  }
  else {
    $dayOfWeek = date('l', $timestamp);
    $sixDays = 60 * 60 * 24 * 6;

    if (($timestamp - $timestampNow) >= $sixDays) {
      $when = "next $dayOfWeek";
    } else {
      $when = "this $dayOfWeek";
    }
  }

  return 'Earthquake Seminar ' . $when . ' - ' . getSpeaker($seminar);
}

/**
 * Use autop to add <p>/<br> tags to summary. All styles must be manually
 *   "inlined" for compatibility (the template already has inlined styles
 *   for content that doesn't require additional HTML tags).
 *
 * @param $seminar {Object}
 *
 * @return $summary {String}
 */
function getSummary ($seminar) {
  $styles = 'color: #ccc; line-height: 1.5; Margin: 0; margin: 0; padding: 12px 0';
  $summary = str_replace('<p>', '<p style="' . $styles . '">', autop($seminar->summary));

  return $summary;
}

/**
 * Get formatted time string (e.g. '10:30 AM')
 *
 * @param $timestamp {Unix timestamp}
 *
 * @return {String}
 */
function getTimeStr ($timestamp) {
  return date('g:i A', $timestamp);
}

/**
 * First, check if email needs to be sent, and if so, assemble data and send it
 *
 * @param $textualTime {String}
 *     English textual datetime description
 * @param $to {String}
 *     email address(es, comma-separated)
 */
function prepare ($textualTime, $to) {
  global $cwd, $db;

  $datetime = strftime('%Y-%m-%d %H:%M:00', strtotime($textualTime));
  $rsSeminars = $db->querySeminars($datetime);

  if ($rsSeminars->rowCount() > 0) {
    $seminar = $rsSeminars->fetch(PDO::FETCH_OBJ);

    // Assume -no seminar- if speaker is empty (committee posts "no seminar" msg on web page)
    if (!$seminar->speaker || ($seminar->publish === 'no')) {
      return;
    }

    $committee = getCommittee();
    $from = sprintf('%s <%s>',
      $committee[0]['name'],
      $committee[0]['email']
    );

    $template = "$cwd/template.html";

    $options = [
      'data' => getData($seminar, $committee),
      'from' => $from,
      'template' => $template,
      'subject' => getSubject($seminar),
      'to' => $to
    ];

    $email = new Email($options);
    $email->send();
  }
}
