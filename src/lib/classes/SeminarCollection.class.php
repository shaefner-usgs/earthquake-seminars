<?php

class SeminarCollection {
  public $seminars;

  public function __construct () {
    $this->seminars = [];
  }

  public function add ($seminar) {
    $this->seminars[] = $seminar;
  }
}
