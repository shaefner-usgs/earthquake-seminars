<?php

/**
 * ESC Seminar Collection
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class SeminarCollection {
  public $seminars;

  public function __construct () {
    $this->seminars = [];
  }

  /**
   * Add a seminar to the collection
   *
   * @param $seminar {Object}
   */
  public function add ($seminar) {
    $this->seminars[] = $seminar;
  }
}
