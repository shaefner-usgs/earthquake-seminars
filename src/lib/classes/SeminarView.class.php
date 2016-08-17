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

  private function _getSeminar () {
    if (!$this->_model->ID) {
      $seminarHtml = '<p class="alert error">ERROR: Seminar Not Found</p>';
    } else {
      $host = '';
      if ($this->_model->host) {
        $host = '<dt>Host:</dt><dd>' . $this->_model->host . '</dd>';
      }
      $note = '';
      if ($this->_model->period === 'live') {
        $note = '<p class="flash"><a href="http://get.adobe.com/flashplayer/">Adobe
          Flash Player</a> is <strong>required</strong> to view live webcasts.</p>';
      }
      $summary = '';
      if ($this->_model->summary) {
        $summary = autop($this->_model->summary); // add <p> tag(s) to summary
      }
      $video = $this->_getVideo();

      $seminarHtml = sprintf('
        <h2>%s</h2>
        %s
        <div class="row %s">
          <div class="column two-of-three video">
            %s
          </div>
          <div class="column one-of-three">
            <h4>%s</h4>
            <p>%s <span class="time">at %s</p>
            <dl>
              <dt class="location">Location:</dt>
              <dd class="location">%s</dd>
              %s
            </dl>
            %s
          </div>
        </div>',
        $this->_model->topic,
        $summary,
        $this->_model->period,
        $video,
        $this->_model->speaker,
        $this->_model->date,
        $this->_model->time,
        $this->_model->location,
        $host,
        $note
      );
    }

    return $seminarHtml;
  }

  private function _getVideo () {
    $video = '';

    if ($this->_model->period === 'past') { // recorded video
      $video = '<video src="' . $this->_model->videoSrc . '" width="100%"
          crossorigin="anonymous" controls="controls">
          <track label="English" kind="captions"
          src="' . $this->_model->videoTrack . '" default="default">
        </video>';
    } else if ($this->_model->period === 'today') { // seminar later today
      $video = '<h3>This seminar will be live streamed today</h3>
        <p>Please reload this page at ' . $this->_model->time . ' to
        watch.</p>';
    } else if ($this->_model->period === 'live') { // live stream
      $video = '<video src="mplive?streamer=rtmp://video2.wr.usgs.gov/live"
          width="100%" controls="controls">
        </video>';
      $video .= '<p><a href="http://video2.wr.usgs.gov:1935/live/mplive/playlist.m3u8">
        View on a mobile device</a></p>';
    }

    return $video;
  }

  public function render () {
    print $this->_getSeminar();
  }
}
