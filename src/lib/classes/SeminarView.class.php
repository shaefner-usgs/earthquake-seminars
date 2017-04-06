<?php

include_once '../lib/_functions.inc.php'; // app functions

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
      $title = $item->title;
      $description = $item->description;

      // jwplayer supports both JWPlayer RSS and  Media RSS namespaces
      $url = '';
      if (is_array($item->xpath('jwplayer:source[1]'))) {
        $media = $item->xpath('jwplayer:source[1]');
        $url = $media[0]['file'];
      } else if (is_array($item->xpath('media:content[1]'))) {
        $media = $item->xpath('media:content[1]');
        $url = $media[0]['url'];
      }
      $dl .= sprintf('<dt><a href="%s">%s</a></dt>
        <dd class="description">%s</dd>',
        $url,
        $title,
        $description
      );
    }
    $dl .= '</dl>';

    $video = $this->_getVideoTag($url);
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
      if ($this->_model->video === 'yes' && $this->_model->status !== 'future') {
        $captions = '<p class="captions">Closed captions are typically available a
          few days after the seminar. To turn them on, press the &lsquo;CC&rsquo;
          button on the video player. For older seminars that don&rsquo;t have
          closed captions, please <a href="mailto:shaefner@usgs.gov">email
          us</a>, and we will do our best to accommodate your request.</p>';
      }
      $dayofweek = date('l', strtotime($this->_model->date));
      $host = '';
      if ($this->_model->host) {
        $host = '<dt class="host">Host:</dt>
          <dd class="host">' . $this->_model->host . '</dd>';
      }
      $flash = '';
      if ($this->_model->video === 'yes' && $this->_model->status === 'live') {
        $flash = '<p class="flash"><a href="http://get.adobe.com/flashplayer/">Adobe
          Flash Player</a> is <strong>required</strong> to view live webcasts.</p>';
      }
      $summary = '';
      if ($this->_model->summary) {
        $summary = autop($this->_model->summary); // add <p> tag(s) to summary
      }
      $video = $this->_getVideo();

      $seminarHtml = sprintf('
        <h2>%s</h2>
        <div class="row %s %s">
          <div class="column two-of-three video">
            %s
          </div>
          <div class="column one-of-three details">
            <h4>%s</h4>
            <p><span class="dayofweek">%s, </span>%s <span class="time">at %s</p>
            <dl>
              <dt class="location">Location:</dt>
              <dd class="location">%s</dd>
              %s
            </dl>
            %s
          </div>
        </div>
        %s
        %s',
        $this->_model->topic,
        $this->_model->category,
        $this->_model->status,
        $video,
        $this->_model->speaker,
        $dayofweek,
        $this->_model->date,
        $this->_model->time,
        $this->_model->location,
        $host,
        $flash,
        $summary,
        $captions
      );
    }

    return $seminarHtml;
  }

  /**
   * Create HTML for video player section based on user's view
   *
   * @return $video {String}
   */
  private function _getVideo () {
    $video = '';

    if ($this->_model->video === 'yes') {
      if ($this->_model->status === 'past') { // recorded video
        if (remoteFileExists($this->_model->videoSrc)) { // mp4 file
          $video = $this->_getVideoTag();
        }
        else if (remoteFileExists($this->_model->videoPlaylist)) { // xml file
          $video = $this->_getPlaylist();
        } else { // no file found
          $video = '<h3>Video not found</h3>
            <p>Please try back later. Videos are usually posted within 24 hours.</p>';
        }
      }
      else if ($this->_model->status === 'today') { // seminar later today
        $video = '<h3>This seminar will be webcast live today</h3>
        <p>Please reload this page at ' . $this->_model->time . ' Pacific.</p>';
      }
      else if ($this->_model->status === 'live') { // live stream
        $video = $this->_getVideoTag();
        $video .= '<p><a href="http://video2.wr.usgs.gov:1935/live/mplive/playlist.m3u8">
        View on a mobile device</a></p>';
      }
    } else {
      $video = '<h3>No webcast</h3>
        <p>This seminar is not available to view online.</p>';
    }

    return $video;
  }

  /**
   * Create <video> tag
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
    if ($this->_model->status === 'past') {
      $videoTag = '<video src="' . $src . '" width="100%"
        crossorigin="anonymous" controls="controls">';

      if (remoteFileExists($this->_model->videoTrack)) { // vtt file
        $videoTag .= '<track label="English" kind="captions"
          src="' . $this->_model->videoTrack . '" default="default" />';
      }

      $videoTag .= '</video>';
    }
    else if ($this->_model->status === 'live') {
      $videoTag = '<video src="mplive?streamer=rtmp://video2.wr.usgs.gov/live"
        width="100%" controls="controls"></video>';
    }

    return $videoTag;
  }

  public function render () {
    print $this->_getSeminar();
  }
}
