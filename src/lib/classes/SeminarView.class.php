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
      $summary = '';
      if ($this->_model->summary) {
        $summary = autop($this->_model->summary); // add <p> tag(s) to summary
      }
      $video = $this->_getVideo();

      $seminarHtml = sprintf('
        <h2>%s</h2>
        %s
        <div class="row %s">
          <div class="column three-of-four">
            %s
          </div>
          <div class="column one-of-four">
            <h4>%s</h4>
            <p>%s</p>
          </div>
        </div>',
        $this->_model->topic,
        $summary,
        $this->_model->period,
        $video,
        $this->_model->speaker,
        $this->_model->date
      );
    }

    return $seminarHtml;
  }

  private function _getVideo () {
    $height = 396;
    $video = '';
    $width = 704;

    if ($this->_model->period === 'past') { // recorded video
      $video = '<video src="' . $this->_model->videoSrc . '" width="' . $width . '"
          height="' . $height . '" crossorigin="anonymous" controls="controls">
          <track label="English" kind="captions"
          src="' . $this->_model->videoTrack . '" default="default">
        </video>';
    } else if ($this->_model->period === 'today') { // seminar later today
      $video = '<h3>This seminar will be live streamed today</h3>
        <p>Please reload this page at ' . $this->_model->time . ' to
        watch.</p>';
    } else if ($this->_model->period === 'live') { // live stream
      $video = '<video src="mplive?streamer=rtmp://video2.wr.usgs.gov/live"
          width="' . $width . '" height="' . $height . '" controls="controls">
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
