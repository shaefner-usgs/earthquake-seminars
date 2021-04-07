<?php

/**
 * Seminar view
 * - creates the HTML for seminar.php
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class SeminarView {
  private $_model;

  public function __construct (Seminar $model) {
    $this->_model = $model;
  }

  /**
   * Parse playlist XML file to create HTML that VideoPlayer.js uses to
   * populate jwplayer's playlist option
   *
   * @return $video {String}
   */
  private function _getPlaylist () {
    $playlist = simplexml_load_file($this->_model->videoPlaylist);

    $dl = '<dl class="playlist">';
    foreach($playlist->channel->item as $item) {
      $captions = '';
      $description = $item->description;
      $mp4 = '';
      $title = $item->title;
      $vtt = '';

      // Get .mp4 file (jwplayer supports both JWPlayer RSS and Media RSS namespaces)
      if (is_array($item->xpath('jwplayer:source[1]'))) {
        $media = $item->xpath('jwplayer:source[1]');
        $mp4 = $media[0]['file'];
      } else if (is_array($item->xpath('media:content[1]'))) {
        $media = $item->xpath('media:content[1]');
        $mp4 = $media[0]['url'];
      }

      // Get .vtt file
      if (is_array($item->xpath('jwplayer:track[1]'))) {
        $media = $item->xpath('jwplayer:track[1]');
        $vtt = $media[0]['file'];
      } else if (is_array($item->xpath('media:subtitle[1]'))) {
        $media = $item->xpath('media:subtitle[1]');
        $vtt = $media[0]['url'];
      }
      if ($vtt) {
        $captions = sprintf('<dd class="captions"><a href="%s">CC</a></dd>', $vtt);
      }

      $dl .= sprintf('<dt>
          <a href="%s">%s</a>
        </dt>
        <dd class="description">%s</dd>
        %s',
        $mp4,
        $title,
        $description,
        $captions
      );
    }

    $dl .= '</dl>';
    $video = $this->_getVideoTag($mp4);
    $video .= $dl;

    return $video;
  }

  /**
   * Create HTML for Seminar
   *
   * @return $seminarHtml {String}
   */
  private function _getSeminar () {
    if (!$this->_model->ID) {
      $seminarHtml = '<p class="alert error">ERROR: Seminar Not Found</p>';
    } else {
      $captions = '';
      $host = '';
      $img = '';
      $summary = '';
      $video = $this->_getVideo();

      if ($this->_model->video === 'yes' && $this->_model->status !== 'future') {
        $captions = '<p class="captions">Closed captions are typically available a
          few days after the seminar. To turn them on, press the &lsquo;CC&rsquo;
          button on the video player. For older seminars that don&rsquo;t have
          closed captions, please <a href="mailto:shaefner@usgs.gov">email
          us</a>, and we will do our best to accommodate your request.</p>';
      }

      if ($this->_model->host) {
        $host = '<dt class="host">Host</dt>
          <dd class="host">' . $this->_model->host . '</dd>';
      }

      if ($this->_model->imageType === 'upload') {
        $img = sprintf('<img src="%s" alt="speaker" class="image" width="%d" />',
          $this->_model->imageUri,
          $this->_model->imageWidth
        );
      }

      if ($this->_model->summary) {
        $summary = autop($this->_model->summary); // add <p> tag(s) to summary
      }

      $seminarHtml = sprintf('
        <h2>%s</h2>
        <div class="row %s">
          <div class="column two-of-three video">
            %s
          </div>
          <div class="column one-of-three details">
            <h4>%s</h4>
            <p><span class="dayofweek">%s, </span>%s <span class="time">at %s</p>
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
        $video,
        $this->_model->speakerWithAffiliation,
        $this->_model->day,
        $this->_model->date,
        $this->_model->time,
        $this->_model->location,
        $host,
        $img,
        $summary,
        $captions
      );
    }

    return $seminarHtml;
  }

  /**
   * Get HTML for video player section based on current time relative to
   *   seminar time
   *
   * @return $video {String}
   */
  private function _getVideo () {
    $video = '';
    $downloadLink = '<a href="https://www.microsoft.com/en-us/microsoft-365/microsoft-teams/download-app">Microsoft Teams</a>';

    if ($this->_model->video === 'yes') {
      if ($this->_model->status === 'past') { // look for recorded video
        if (remoteFileExists($this->_model->videoSrc)) { // mp4 file
          $video = $this->_getVideoTag();
        }
        else if (remoteFileExists($this->_model->videoPlaylist)) { // xml (playlist) file
          $video = $this->_getPlaylist();
        } else { // no video file
          $video = '<div class="alert info">
              <h3>Video not found</h3>
              <p>Please check back later. Videos are usually posted within 24 hours.</p>
            </div>';
        }
      }
      else if ($this->_model->status === 'today') { // seminar later today
        $video = '<div class="alert info">
            <h3>This seminar will be live-streamed today</h3>
            <p><a href="' . $GLOBALS['TEAMS_LINK'] . '">View the live-stream</a>
              starting at ' . $this->_model->time . ' Pacific (requires ' .
              $downloadLink . ').</p>
          </div>';
      }
      else if ($this->_model->status === 'live') { // live now
        $video = '<div class="alert info">
            <h3>Live now</h3>
            <p><a href="' . $GLOBALS['TEAMS_LINK'] . '">View the live-stream</a>
              (requires ' . $downloadLink . ').</p>
          </div>';
      }
    } else {
      $video = '<div class="alert info">
          <h3>No webcast</h3>
          <p>This seminar is not available to view online.</p>
        </div>';
    }

    return $video;
  }

  /**
   * Get <video> tag
   *
   * @param $src {String} default is NULL
   *     use provided $src or obtain from the model
   *
   * @return $videoTag {String}
   */
  private function _getVideoTag ($src=NULL) {
    if (!$src) {
      $src = $this->_model->videoSrc;
    }
    $videoTag = '<video src="' . $src . '" width="100%" controls="controls"
      crossorigin="anonymous" poster="img/poster.png">';

    if (remoteFileExists($this->_model->videoTrack)) { // vtt file
      $videoTag .= '<track label="English" kind="captions"
        src="' . $this->_model->videoTrack . '" default="default" />';
    }

    $videoTag .= '</video>';

    return $videoTag;
  }

  public function render () {
    print $this->_getSeminar();
  }
}
