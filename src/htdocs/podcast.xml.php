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

$baseUri = 'http://earthquake.usgs.gov/contactus/menlo/seminars';
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

/**
 * Convert all applicable characters to plain text or html entities
 */
function msWordEntities ($str, $style='plain') {
  // first 7 are Windows Extended ASCII codes;
  // next 7 are Extended ASCII codes;
  // last 7 are UTF-8 codes
  $search = array (
    chr(212), chr(213), chr(210), chr(211), chr(208), chr(209), chr(201),
    chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133),
    chr(0xe2) . chr(0x80) . chr(0x98),
      chr(0xe2) . chr(0x80) . chr(0x99),
      chr(0xe2) . chr(0x80) . chr(0x9c),
      chr(0xe2) . chr(0x80) . chr(0x9d),
      chr(0xe2) . chr(0x80) . chr(0x93),
      chr(0xe2) . chr(0x80) . chr(0x94),
      chr(0xe2) . chr(0x80) . chr(0xa6)
  );

  if ($style == 'smart') { // Replace special chars w/ html entity equivalents
    $replace = array (
      '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8211;', '&#8212;', '&#8230;',
      '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8211;', '&#8212;', '&#8230;',
      '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8211;', '&#8212;', '&#8230;'
    );
  } else {
    $replace = array ( // Replace special chars w/ plain text equivalents
      "'", "'", '"', '"', '--', '---', '...',
      "'", "'", '"', '"', '--', '---', '...',
      "'", "'", '"', '"', '--', '---', '...'
    );
  }

  return str_ireplace($search, $replace, $str);
}

function setHeaders () {
  header('Content-Type: application/xml');
}

/**
 * Convert all applicable characters to XML entities
 */
function xmlEntities ($str) {
  $search = array(
    '&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;', '&cent;',
    '&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;',
    '&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;',
    '&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;',
    '&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;',
    '&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;',
    '&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;',
    '&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;',
    '&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;',
    '&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;',
    '&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;',
    '&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;',
    '&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;',
    '&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;'
  );
  $replace = array(
    '&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;','&#162;','&#163;',
    '&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;',
    '&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;',
    '&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;',
    '&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;',
    '&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;',
    '&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;',
    '&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;',
    '&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;',
    '&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;',
    '&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;',
    '&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;',
    '&#252;','&#253;','&#254;','&#255;'
  );

  $str = msWordEntities($str);
  $str = htmlentities($str);
  $str = str_ireplace($search, $replace, $str);

  return $str;
}
