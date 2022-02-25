<?php

include_once __DIR__ . '/../_functions.inc.php'; // app functions

/**
 * Create the RSS (podcast) feed.
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 *
 * @param $options {Array}
 *   [
 *      'collection': {Object} // seminars
 *      'template': {String} // full path to RSS template file
 *   ]
 */
class Feed {
  private $_baseUri,
          $_buildDate,
          $_collection,
          $_template;

  public function __construct($options) {
    global $DATA_HOST, $MOUNT_PATH;

    $this->_baseUri = "https://$DATA_HOST$MOUNT_PATH";
    $this->_collection = $options['collection'];
    $this->_template = $options['template'];
  }

  /**
   * Create the feed.
   *
   * @return $feed {String}
   */
  private function _create () {
    $items = implode("\n", $this->_getItems()); // sets $this->_buildDate
    $data = [
      'base-uri' => $this->_baseUri,
      'build-date-rfc' => $this->_buildDate,
      'items' => $items,
      'pub-date-rfc' => date('D, j M Y H:i:s T')
    ];
    $feed = file_get_contents($this->_template);

    // Substitute feed data for mustache placeholders
    foreach ($data as $key => $value) {
      $pattern = '{{' . $key . '}}';
      $feed = str_replace($pattern, $value, $feed);
    }

    return $feed;
  }

  /**
   * Get the XML content for a feed <item>.
   *
   * @param $seminar {Object}
   *
   * @return {String}
   */
  private function _getItem ($seminar) {
    global $DATA_DIR, $DATA_HOST;

    $path = sprintf('%s/%s/%s.mp4',
      $DATA_DIR,
      $seminar->year,
      date('Ymd', $seminar->timestamp)
    );
    $filesize = filesize($path);
    $guid = sprintf('%s/%s.mp4',
      $seminar->year,
      date('Ymd', strtotime($seminar->datetime))
    );
    $link = $this->_baseUri . '/' . $seminar->ID;
    $speaker = xmlEntities($seminar->speakerWithAffiliation);
    $summary = xmlEntities($seminar->summary);
    $topic = xmlEntities($seminar->topic);
    $url = "https://$DATA_HOST" . $seminar->videoSrc;

    return sprintf('
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
        <media:thumbnail url="%s/img/podcast-small.png" />
      </item>',
      $speaker,
      $link,
      $topic,
      $guid,
      $seminar->pubDate,
      $url,
      $filesize,
      $speaker,
      $topic,
      $summary,
      $this->_baseUri,
      $this->_baseUri
    );
  }

  /**
   * Get the <item>s for the feed body.
   *
   * @return $items {Array}
   */
  private function _getItems () {
    $count = 0;
    $items = [];

    foreach ($this->_collection->seminars as $seminar) {
      if ($count === 10) break; // max 10 (seminars w/o videos are skipped)

      if ($seminar->videoSrc) {
        $count ++;
        $items[] = $this->_getItem($seminar);

        if ($count === 1) {
          $this->_buildDate = $seminar->pubDate; // most recent seminar
        }
      }
    }

    return $items;
  }

  /**
   * Render the feed.
   */
  public function render () {
    print $this->_create();
  }
}
