#!/usr/bin/php
<?php

/**
 * PURPOSE: script sends out email announcements of upcoming seminars. Called
 *   by crontab (esc user) every 15 minutes
 */

$cwd = dirname(__FILE__);

include_once "$cwd/../../conf/config.inc.php"; // app config
include_once "$cwd/../_autop.inc.php"; // utility function that creates <p>s and <br>s

include_once "$cwd/../classes/Seminar.class.php"; // model
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
// prepare('2020-10-21 10:30:00', $ADMIN_EMAIL);


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
 * Get key-value pairs used to populate email template with seminar details
 *
 * @param $seminar {Object}
 * @param $committee {Array}
 *
 * @return {Array}
 */
function getData ($seminar, $committee) {
  global $TEAMS_LINK;

  $displayButton = 'block';
  $displayHost = 'block';
  $url = 'https://earthquake.usgs.gov/contactus/menlo/seminars/' . $seminar->ID;
  $videoText = 'You can also watch the <a href="' . $url . '">recorded talk</a> later in the archives.';

  if (!$seminar->host) {
    $displayHost = 'none';
  }
  if (!$seminar->video) {
    $displayButton = 'none';
    $videoText = 'This seminar will not be live-streamed.';
  }

  return [
    'affiliation' => replaceChars($seminar->affiliation),
    'current-year' => date('Y'),
    'date' =>  $seminar->dayDate,
    'display-button' => $displayButton,
    'display-host' => $displayHost,
    'email1' => $committee[0]['email'],
    'email2' => $committee[1]['email'],
    'host' => $seminar->host,
    'id' => $seminar->ID,
    'location' => $seminar->location,
    'name1' => $committee[0]['name'],
    'name2' => $committee[1]['name'],
    'phone1' => $committee[0]['phone'],
    'phone2' => $committee[1]['phone'],
    'speaker' => $seminar->speaker,
    'speakerWithAffiliation' => replaceChars($seminar->speakerWithAffiliation),
    'summary' => getSummary($seminar),
    'teams-link' => $TEAMS_LINK,
    'time' => "$seminar->time Pacific",
    'topic' => replaceChars($seminar->topic),
    'video-text' => $videoText
  ];
}

/**
 * Get email subject
 *
 * @param $seminar {Object}
 *
 * @return {String}
 */
function getSubject ($seminar) {
  $timestampNow = time();
  $todaysDate = date('F j, Y', $timestampNow);

  // Get relative time
  if ($seminar->date === $todaysDate) {
    $when = "today at $seminar->time";
  }
  else {
    $sixDays = 60 * 60 * 24 * 6;

    if (($seminar->timestamp - $timestampNow) >= $sixDays) {
      $when = "next $seminar->day";
    } else {
      $when = "this $seminar->day";
    }
  }

  return "Earthquake Seminar $when - $seminar->speakerWithAffiliation";
}

/**
 * Use autop to add <p>/<br> tags to summary.
 *
 * $styles (css styles) will be set inline using a modified version of autop
 *
 * @param $seminar {Object}
 *
 * @return $summary {String}
 */
function getSummary ($seminar) {
  $styles = 'color: #333; line-height: 1.4; Margin:0; Margin-bottom:10px; ' .
    'margin:0; margin-bottom:1em; margin-top:1em; padding-bottom:0; ' .
    'padding-left:0; padding-right:0; padding-top:0;';

  return autop(replaceChars($seminar->summary), true, $styles);
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
    $rsSeminars->setFetchMode(PDO::FETCH_CLASS, 'Seminar');
    $seminar = $rsSeminars->fetch();

    // Assume -no seminar- if speaker is empty (committee posts "no seminar" msg on web page)
    if (!$seminar->speaker || (!$seminar->publish)) {
      return;
    }

    $committee = getCommittee();
    $from = sprintf('%s <%s>',
      $committee[0]['name'],
      $committee[0]['email']
    );

    $email = new Email([
      'data' => getData($seminar, $committee),
      'from' => $from,
      'template' => "$cwd/template.html",
      'subject' => getSubject($seminar),
      'to' => $to
    ]);
    $email->send();
  }
}

/**
 * Replace special chars. with HTML entities
 *
 * @param $str {String}
 *
 * @return {String}
 */
function replaceChars ($str) {
  $specialChars = [
    '‘',
    '’',
    '“',
    '”'
  ];
  $entities = [
    '&lsquo;',
    '&rsquo;',
    '&ldquo;',
    '&rdquo;'
  ];

  return str_replace($specialChars, $entities, $str);
}
