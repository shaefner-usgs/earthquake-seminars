#!/usr/bin/php
<?php

/**
 * PURPOSE: script sends out email announcements of upcoming seminars. It is
 * executed by crontab (esc user) every 15 minutes.
 */

$cwd = dirname(__FILE__);

include_once "$cwd/../../conf/config.inc.php"; // app config
include_once "$cwd/../_autop.inc.php"; // utility function that creates <p>s and <br>s
include_once "$cwd/../classes/Db.class.php"; // db connector, queries
include_once "$cwd/../classes/Email.class.php"; // creates, sends email
include_once "$cwd/../classes/SeminarCollection.class.php"; // collection

// 2.5 hour announcement
prepare('+150 minutes', $USGS_EMAIL);

// 2 day announcement
prepare('+2 days', $USGS_EMAIL);

// 7 day announcement (sends to NASA only)
prepare('+7 days', $NASA_EMAIL);

// Test announcement
// prepare('2022-02-09 10:30:00', $ADMIN_EMAIL);


/**
 * Get the seminar committee members.
 *
 * @return $committee {Array}
 */
function getCommittee () {
  $db = new Db;
  $committee = [];
  $rsCommittee = $db->queryCommittee();

  while ($coChair = $rsCommittee->fetch(PDO::FETCH_OBJ)) {
    $committee[] = [
      'email' => $coChair->email,
      'name' => $coChair->name
    ];
  }

  return $committee;
}

/**
 * Get the key-value pairs used to populate the email template.
 *
 * @param $seminar {Object}
 *
 * @return {Array}
 */
function getData ($seminar) {
  global $DATA_HOST, $MOUNT_PATH, $TEAMS_LINK;

  $committee = getCommittee();
  $date = sprintf('%s, %s %d%s',
    $seminar->weekday,
    $seminar->month,
    $seminar->day,
    $seminar->dayOrdinal
  );
  $displayButton = 'block';
  $displayHost = 'block';
  $urlBase = "https://$DATA_HOST$MOUNT_PATH";
  $videoText = 'You can also watch the <a href="' . $urlBase . '/$seminar->ID">' .
    'recorded talk</a> later in the archives.';

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
    'date' =>  $date,
    'display-button' => $displayButton,
    'display-host' => $displayHost,
    'email1' => $committee[0]['email'],
    'email2' => $committee[1]['email'],
    'host' => replaceChars($seminar->host),
    'id' => $seminar->ID,
    'image' => getImage($seminar),
    'location' => $seminar->location,
    'name1' => replaceChars($committee[0]['name']),
    'name2' => replaceChars($committee[1]['name']),
    'speaker' => replaceChars($seminar->speaker),
    'speakerWithAffiliation' => $seminar->speakerWithAffiliation, // for subject
    'summary' => getSummary($seminar),
    'teams-link' => $TEAMS_LINK,
    'time' => "$seminar->time Pacific",
    'topic' => replaceChars($seminar->topic),
    'url-base' => $urlBase,
    'video-text' => $videoText
  ];
}

/**
 * Get the HTML for an uploaded image if it exists.
 *
 * @param $seminar {Object}
 *
 * $return $img {String}
 */
function getImage ($seminar) {
  global $DATA_HOST;

  $img = '';
  $style = 'border: none; display: block; float: left; margin: 10px 10px 10px 0; outline: 0;';

  if ($seminar->imageType === 'upload') {
    $img = sprintf('<img src="https://%s%s" alt="speaker" style="%s" width="%d" />',
      $DATA_HOST,
      $seminar->imageSrc,
      $style,
      $seminar->imageWidth * .75 // reduce slightly
    );
  }

  return $img;
}

/**
 * Get the email subject.
 *
 * @param $seminar {Object}
 *
 * @return {String}
 */
function getSubject ($seminar) {
  $seminarDate = date('F j, Y', $seminar->timestamp);
  $timestampNow = time();
  $todaysDate = date('F j, Y', $timestampNow);

  // Get relative time
  if ($seminarDate === $todaysDate) {
    $when = "today at $seminar->time";
  } else {
    $sixDays = 60 * 60 * 24 * 6;

    if (($seminar->timestamp - $timestampNow) >= $sixDays) {
      $when = "next $seminar->weekday";
    } else {
      $when = "this $seminar->weekday";
    }
  }

  return "ESC Seminar $when - $seminar->speakerWithAffiliation";
}

/**
 * Use autop to add <p>/<br> tags to the summary. $styles (CSS styles) are set
 * inline using a modified version of autop.
 *
 * @param $seminar {Object}
 *
 * @return {String}
 */
function getSummary ($seminar) {
  $styles = 'color: #333; line-height: 1.4; Margin:0; Margin-bottom:10px; ' .
    'margin:0; margin-bottom:1em; margin-top:1em; padding-bottom:0; ' .
    'padding-left:0; padding-right:0; padding-top:0;';

  return autop(replaceChars($seminar->summary), true, $styles);
}

/**
 * First, check if email needs to be sent, and if so, assemble data and send it.
 *
 * @param $timeDescription {String}
 *     English textual datetime description
 * @param $to {String}
 *     email address(es, comma-separated)
 */
function prepare ($timeDescription, $to) {
  global $cwd;

  $datetime = strftime('%Y-%m-%d %H:%M:00', strtotime($timeDescription));
  $seminarCollection = new seminarCollection();
  $seminarCollection->addSeminarAtTime($datetime);

  if ($seminarCollection->seminars) {
    $seminar = $seminarCollection->seminars[0];

    // Assume no seminar if speaker is empty (committee posts "no seminar" msg on web page)
    if (!$seminar->speaker || (!$seminar->publish)) {
      return;
    }

    $data = getData($seminar);
    $from = sprintf('%s <%s>',
      $data['name1'],
      $data['email1']
    );
    $email = new Email([
      'data' => $data,
      'from' => $from,
      'subject' => getSubject($seminar),
      'template' => "$cwd/template.html",
      'to' => $to
    ]);

    $email->send();
  }
}

/**
 * Replace special chars. with HTML entities while preserving HTML tags
 *
 * @param $str {String}
 *
 * @return {String}
 */
function replaceChars ($str) {
  return htmlspecialchars_decode(
    htmlentities($str, ENT_NOQUOTES, 'UTF-8', false),
    ENT_NOQUOTES
  );
}
