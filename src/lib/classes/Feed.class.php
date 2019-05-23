<?php

include_once __DIR__ . '/../_functions.inc.php'; // app functions

/**
 * Create RSS (Podcast) Feed
 *
 * @param $options {Array}
 *   [
 *      'baseUri': {String} // seminar web page URL
 *      'collection': {Object} // seminar details from database
 *      'template': {String} // full path to RSS template file
 *   ]
 */
class Feed {
  private $_baseUri, $_buildDate, $_collection, $_data, $_feed, $_template;

  public function __construct($options) {
    $this->_baseUri = $options['baseUri'];
    $this->_collection = $options['collection'];
    $this->_template = $options['template'];

    $this->_data = [
      'base-uri' => $this->_baseUri,
      'items' => implode("\n", $this->_getItems()), // string of feed <item>s
      'build-date-rfc' => $this->_buildDate,
      'pub-date-rfc' => date('D, j M Y H:i:s T')
    ];
    $this->_feed = $this->_getTemplate();

    $this->_createFeed(); // Create the feed
  }

  /**
   * Create RSS Feed
   */
  private function _createFeed () {
    // Substitute feed data for mustache placeholders
    foreach ($this->_data as $key => $value) {
      $pattern = '{{' . $key . '}}';
      $this->_feed = str_replace($pattern, $value, $this->_feed);
    }
  }

  /**
   * Create feed <item>
   *
   * @param $seminar {Object}
   * @param $firstItem {Boolean}
   *     whether this seminar is first in the list (i.e. the 'latest' seminar)
   *
   * @return $item {String}
   */
  private function _createItem ($seminar, $firstItem = false) {
    $filesize = remoteFileExists($seminar->videoSrc);
    $link = $this->_baseUri . '/' . $seminar->ID;
    $pubDate = date('D, j M Y H:i:s T', strtotime($seminar->datetime));
    $speaker = xmlEntities($seminar->speaker);
    $summary = xmlEntities($seminar->summary);
    $topic = xmlEntities($seminar->topic);

    $item = sprintf('<item>
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
        <media:thumbnail url="%s/img/podcast-small.png" />
      </item>',
      $speaker,
      $link,
      $topic,
      $seminar->videoSrc,
      $pubDate,
      $seminar->videoSrc,
      $filesize,
      $speaker,
      $topic,
      $summary,
      $this->_baseUri,
      $this->_baseUri
    );

    // Set <lastBuildDate> for feed to latest seminar's <pubDate>
    if ($firstItem) {
      $this->_buildDate = $pubDate;
    }

    return $item;
  }

  /**
   * Get RSS <item>s for feed body
   *
   * @return $items {Array}
   */
  private function _getItems () {
    $count = 0;
    $items = [];

    foreach ($this->_collection->seminars as $seminar) {
      $firstItem = false;

      // Don't incl. more than 10 (loop thru more b/c we skip seminars w/o videos)
      if ($count === 10) break;

      if (remoteFileExists($seminar->videoSrc)) {
        $count ++;
        if ($count === 1) {
          $firstItem = true;
        }
        $items[] = $this->_createItem($seminar, $firstItem);
      }
    }

    return $items;
  }

  /**
   * Read xml feed template into a string and return it
   *
   * @return {String}
   */
  private function _getTemplate() {
    return file_get_contents($this->_template);
  }

  /**
   * Render feed data
   */
  public function render () {
    print $this->_feed;
  }
}
