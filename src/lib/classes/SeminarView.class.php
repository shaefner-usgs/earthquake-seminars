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

  private function _getVideoTag () {
    if ($this->_model->live) {
      $videoSrc = 'mplive?streamer=rtmp://video2.wr.usgs.gov/live';
      $video = '<video src="' . $videoSrc . '" width="704" height="396"
          controls="controls">
        </video>';
    } else {
      $video = '<video src="' . $this->_model->videoSrc . '" width="704"
          height="396" crossorigin="anonymous" controls="controls">
          <track label="English" kind="captions"
          src="' . $this->_model->videoTrack . '" default="default">
        </video>';
    }

    return $video;
  }

  private function _getSeminar () {
    if (!$this->_model->ID) {
      $seminarHtml = '<p class="alert error">ERROR: Seminar Not Found</p>';
    } else {
      $summary = '';
      if ($this->_model->summary) {
        $summary =  autop($this->_model->summary);
      }

      $seminarHtml = sprintf('
        <h2>%s</h2>
        %s
        <div class="row">
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
        $this->_getVideoTag(),
        $this->_model->speaker,
        $this->_model->date
      );
    }

    return $seminarHtml;
  }

  public function render () {
    print $this->_getSeminar();
  }
}
