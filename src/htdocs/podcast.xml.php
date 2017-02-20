<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Seminar.class.php'; // model

$db = new Db();

// Db query result: past 12 seminars with a video
$rsSeminars = $db->queryPodcastVideos();

$rsSeminars->setFetchMode(PDO::FETCH_CLASS, Seminar);
$seminars = $rsSeminars->fetchAll();

$baseUri = 'https://earthquake.usgs.gov/contactus/menlo/seminars';
$latestTimestamp = ''; // timestamp of latest seminar; set in getBody()

$body = getBody($baseUri, $seminars);
$header = getHeader($baseUri, $latestTimestamp);
$footer = getFooter();

setHeaders();

print "$header\n$body\n$footer";


function getBody ($baseUri, $seminars) {
  $body = '';
  $count = 0;

  foreach ($seminars as $seminar) {
    // Don't incl. more than 10 (loop thru more b/c we skip seminars w/o videos)
    if ($count === 10) break;

    $filesize = remoteFileExists($seminar->videoSrc);
    if ($filesize) {
      $count ++;
      $timestamp = strtotime($seminar->datetime);

      if ($count === 1) { // set timestamp value to latest seminar
        $GLOBALS['latestTimestamp'] = $timestamp;
      }

      $pubDate_rfc = date('D, j M Y H:i:s T', $timestamp);
      $speaker = xmlEntities($seminar->speaker);
      $summary = xmlEntities($seminar->summary);
      $topic = xmlEntities($seminar->topic);
      $url = sprintf ("$baseUri/%d", $seminar->ID);

      $body .= sprintf('
        <item>
          <title>%s</title>
          <link>%s</link>
          <description>%s</description>
          <guid>%s</guid>
          <pubDate>%s</pubDate>
          <enclosure url="%s" length="%s" type="video/mp4" />
          <itunes:author>%s</itunes:author>
          <itunes:duration>60:00</itunes:duration>
          <itunes:explicit>no</itunes:explicit>
          <itunes:subtitle>%s</itunes:subtitle>
          <itunes:summary>%s</itunes:summary>
          <itunes:image href="%s/img/podcast.png?20160901" />
          <media:thumbnail url="%s/img/podcast.png"/>
        </item>',
        $speaker,
        $url,
        $topic,
        $seminar->videoSrc,
        $pubDate_rfc,
        $seminar->videoSrc,
        $filesize,
        $speaker,
        $topic,
        $summary,
        $baseUri,
        $baseUri
      );
    }
  }

  return $body;
}

function getFooter () {
  return '</channel>
    </rss>';
}

function getHeader ($baseUri, $timestamp) {
  $buildDate_rfc = date('D, j M Y H:i:s T', $timestamp);
  $pubDate_rfc = date('D, j M Y H:i:s T');

  $header = '<?xml version="1.0" encoding="UTF-8"?>
    <rss xmlns:content="http://purl.org/rss/1.0/modules/content/"
      xmlns:atom="http://www.w3.org/2005/Atom"
      xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
      xmlns:media="http://search.yahoo.com/mrss/" version="2.0">
    <channel>
      <atom:link href="' . $baseUri . '/feed" rel="self"
        type="application/rss+xml" />
      <title>USGS Earthquake Science Center Seminars</title>
      <link>' . $baseUri . '</link>
      <description>Open dialogue about important issues in earthquake science
        presented by Center scientists, visitors, and invitees.</description>
      <docs>http://blogs.law.harvard.edu/tech/rss</docs>
      <language>en-us</language>
      <pubDate>' . $pubDate_rfc . '</pubDate>
      <lastBuildDate>' . $buildDate_rfc . '</lastBuildDate>
      <itunes:author>USGS</itunes:author>
      <itunes:category text="Science &amp; Medicine">
        <itunes:category text="Natural Sciences" />
      </itunes:category>
      <itunes:category text="Education" />
      <itunes:category text="Government &amp; Organizations" />
      <itunes:explicit>no</itunes:explicit>
      <itunes:image href="' . $baseUri . '/img/podcast.png?20160901" />
      <itunes:keywords>USGS, science, earthquake, seminar, seismology, hazards,
        prepare, tectonics, tsunami</itunes:keywords>
      <itunes:owner>
      <itunes:name>USGS, Menlo Park (Scott Haefner)</itunes:name>
      <itunes:email>shaefner@usgs.gov</itunes:email>
      </itunes:owner>
      <itunes:subtitle>Earthquake Science Center</itunes:subtitle>
      <itunes:summary>Open dialogue about important issues in earthquake science
        presented by Center scientists, visitors, and invitees.</itunes:summary>';

  return $header;
}

function setHeaders () {
  header('Content-Type: application/xml');
}
