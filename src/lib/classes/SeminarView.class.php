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
    return '<video src="' . $this->_model->videoSrc . '" width="704"
      height="396" crossorigin="anonymous" controls="controls">
        <track label="English" kind="captions"
          src="' . $this->_model->videoTrack . '" default="default">
    </video>';
  }

  public function render () {
    print '<h2>' . $this->_model->topic . '</h2>';
    if ($this->_model->summary) {
      print autop($this->_model->summary);
    }
    print '<div class="row">';
    print '  <div class="column three-of-four">';
    print $this->_getVideoTag();
    print '  </div>';
    print '  <div class="column one-of-four">';
    print '  <h4>' . $this->_model->speaker . '</h4>';
    print '   <p>' . $this->_model->date . '</p>';
    print '  </div>';
    print '</div>';
  }
}
