<?php

/**
 * Create the HTML for the given seminar.
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 *
 * @param $model {Object}
 */
class SeminarView {
  private $_model;

  public function __construct (Seminar $model) {
    $this->_model = $model;
  }

  /**
   * Create the HTML for the view.
   *
   * @return $html {String}
   */
  private function _create () {
    if ($this->_model->ID) {
      $content = $this->_getContent();
      $html = sprintf('
        <h2>%s</h2>
        <div class="row %s">
          <div class="column two-of-three video">
            %s
          </div>
          <div class="column one-of-three details">
            <h4>%s</h4>
            <p>
              <span class="dayofweek">%s<span>, </span></span>
              <span class="date">%s %d, %d</span>
              <span class="time">at %s</span>
            </p>
            <dl>
              <dt class="location">Location</dt>
              <dd class="location">%s</dd>
              %s
            </dl>
          </div>
        </div>
        %s
        %s
        %s',
        $this->_model->topic,
        $this->_model->status,
        $content['video'],
        $this->_model->speakerWithAffiliation,
        $this->_model->weekday,
        $this->_model->month,
        $this->_model->day,
        $this->_model->year,
        $this->_model->time,
        $this->_model->location,
        $content['host'],
        $content['img'],
        $content['summary'],
        $content['captions']
      );
    } else {
      $html = '<p class="alert error">ERROR: Seminar not found</p>';
    }

    return $html;
  }

  /**
   * Get the generated content based on the model's values.
   *
   * @return {Array}
   */
  private function _getContent () {
    $captions = '';
    $host = '';
    $img = '';
    $summary = '';

    if ($this->_model->video === 'yes' && preg_match('/past/', $this->_model->status)) {
      $captions = '<p class="captions">Closed captions are typically available a
        few days after the seminar. To turn them on, press the &lsquo;CC&rsquo;
        button on the video player. For older seminars that don&rsquo;t have
        closed captions, please <a href="mailto:shaefner@usgs.gov">email
        us</a>, and we will do our best to accommodate your request.</p>';
    }

    if ($this->_model->host) {
      $host = '<dt class="host">Host</dt>';
      $host .= '<dd class="host">' . $this->_model->host . '</dd>';
    }

    if ($this->_model->imageType === 'upload') { // skip default podcast img
      $img = sprintf('<img src="%s" alt="speaker" class="image" width="%d" />',
        $this->_model->imageSrc,
        $this->_model->imageWidth
      );
    }

    if ($this->_model->summary) {
      $summary = autop($this->_model->summary);
    }

    return [
      'captions' => $captions,
      'host' => $host,
      'img' => $img,
      'summary' => $summary,
      'video' => $this->_getVideoSection()
    ];
  }

  /**
   * Get the mp4/vtt files from the playlist XML for a given item.
   * Note: jwplayer supports both JWPlayer and Media RSS namespaces.
   *
   * @param $item {Object}
   *
   * @return {Array}
   */
  private function _getFiles ($item) {
    $mp4 = '';
    $vtt = '';

    if (is_array($item->xpath('jwplayer:source[1]'))) {
      $media = $item->xpath('jwplayer:source[1]');
      $mp4 = $media[0]['file'];
    } else if (is_array($item->xpath('media:content[1]'))) {
      $media = $item->xpath('media:content[1]');
      $mp4 = $media[0]['url'];
    }

    if (is_array($item->xpath('jwplayer:track[1]'))) {
      $media = $item->xpath('jwplayer:track[1]');
      $vtt = $media[0]['file'];
    } else if (is_array($item->xpath('media:subtitle[1]'))) {
      $media = $item->xpath('media:subtitle[1]');
      $vtt = $media[0]['url'];
    }

    return [
      'mp4' => $mp4,
      'vtt' => $vtt
    ];
  }

  /**
   * Parse the playlist XML file to create the HTML that VideoPlayer.js uses to
   * populate jwplayer's playlist option.
   *
   * @return $html {String}
   */
  private function _getPlaylist () {
    global $DATA_DIR;

    $html = '<dl class="playlist">';
    $path = sprintf('%s/%s/%s.xml',
      $DATA_DIR,
      $this->_model->year,
      date('Ymd', $this->_model->timestamp)
    );
    $playlist = simplexml_load_file($path);

    foreach($playlist->channel->item as $item) {
      $captions = '';
      $files = $this->_getFiles($item);

      if ($files['vtt']) {
        $captions = sprintf('<dd class="captions"><a href="%s">CC</a></dd>',
          $files['vtt']
        );
      }

      $html .= sprintf('
        <dt>
          <a href="%s">%s</a>
        </dt>
        <dd class="description">%s</dd>
        %s',
        $files['mp4'],
        $item->title,
        $item->description,
        $captions
      );
    }

    $html .= '</dl>';
    $html .= $this->_getVideo($files['mp4']);

    return $html;
  }

  /**
   * Get the HTML for the video tag.
   *
   * @param $src {String}
   *
   * @return {String}
   */
  private function _getVideo ($src) {
    $track = '';

    if ($this->_model->trackSrc) { // vtt file
      $track = sprintf('
        <track label="English" kind="captions" src="%s" default="default" />',
        $this->_model->trackSrc
      );
    }

    return sprintf('
      <video src="%s" width="100%%" controls="controls" crossorigin="anonymous"
        poster="img/poster.png">%s</video>',
      $src,
      $track
    );
  }

  /**
   * Get the HTML for the video player section based on the current time
   * relative to the seminar's time.
   *
   * @return $html {String}
   */
  private function _getVideoSection () {
    global $TEAMS_LINK;

    $downloadLink = 'https://www.microsoft.com/en-us/microsoft-365/microsoft-teams/download-app';
    $html = '';

    if ($this->_model->video === 'yes') {
      if (preg_match('/past/', $this->_model->status)) { // past seminar
        if ($this->_model->videoSrc) { // mp4 file
          $html = $this->_getVideo($this->_model->videoSrc);
        } else if ($this->_model->playlistSrc) { // xml (playlist) file
          $html = $this->_getPlaylist();
        } else { // no video file
          $html = '
            <div class="alert info">
              <h3>Video not found</h3>
              <p>Please check back later. Videos are usually posted within 24
                hours.</p>
            </div>';
        }
      } else if ($this->_model->status === 'today') { // seminar later today
        $html = sprintf('
          <div class="alert info">
            <h3>This seminar will be live-streamed today</h3>
            <p>
              <a href="%s">View the live-stream</a> starting at %s Pacific
              (requires <a href="%s">Microsoft Teams</a>).
            </p>
          </div>',
          $TEAMS_LINK,
          $this->_model->time,
          $downloadLink
        );
      } else if ($this->_model->status === 'live') { // live now
        $html = sprintf('
          <div class="alert info">
            <h3>Live now</h3>
            <p>
              <a href="%s">View the live-stream</a>
              (requires <a href="%s">Microsoft Teams</a>).
            </p>
          </div>',
          $TEAMS_LINK,
          $downloadLink
        );
      }
    } else {
      $html = '
        <div class="alert info">
          <h3>No webcast</h3>
          <p>This seminar is not available to view online.</p>
        </div>';
    }

    return $html;
  }

  /**
   * Render the view.
   */
  public function render () {
    print $this->_create();
  }
}
