<?php

/**
 * Seminar view
 * - creates the HTML for seminar.php
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class SeminarView {
  private $_model;

  public function __construct ($model) {
    $this->_model = $model;
  }

  private function _getVideoTag () {
    return '<video src="' . $this->_model->video . '" width="704"
      height="396" crossorigin="anonymous" controls="controls">
        <track label="English" kind="captions"
          src="' . $this->_model->captions . '" default>
    </video>';
  }

  public function render () {
    print '<h2>' . $this->_model->topic . '</h2>';
    if ($this->_model->summary) {
      print autop($this->_model->summary);
    }

    $video = $this->_getVideoTag();

    print $video;
  }
}
