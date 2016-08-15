<?php

/**
 * Model for ESC Seminar
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Seminar {
  private $_data = [];

  public function __construct () {
    // Add non-db fields to model
    if ($this->_data['datetime']) {
      $datetime = $this->_data['datetime'];
      $timestamp = strtotime($datetime);
      $year = date('Y', $timestamp);

      $videoDomain = 'http://media.wr.usgs.gov';
      $videoFile = str_replace('-', '', substr($datetime, 0, 10)) . '.mp4';
      $videoPath = '/ehz/' . $year;

      $this->_data['date'] = date('l, F j', $timestamp);
      $this->_data['dateShort'] = date('D, M j', $timestamp);
      $this->_data['day'] = date('l', $timestamp);
      $this->_data['live'] = $this->_isLive($timestamp);
      $this->_data['month'] = date('F', $timestamp);
      $this->_data['time'] = date('g:i A', $timestamp);
      $this->_data['timestamp'] = $timestamp;
      $this->_data['videoSrc'] = $videoDomain . $videoPath . '/' . $videoFile;
      $this->_data['videoTrack'] = str_replace('mp4', 'vtt', $this->_data['videoSrc']);
      $this->_data['year'] = $year;

      $this->_addAffiliation();
    }
  }

  // add affiliation to speaker field
  private function _addAffiliation () {
    $speaker = $this->_data['speaker'];
    if ($this->_data['affiliation']) {
      $speaker .= ', ' . $this->_data['affiliation'];
      $this->_data['speaker'] = $speaker;
    }
  }

  private function _isLive ($seminarStart) {
    $buffer = 5 * 60; // 5 mins
    $isLive = false;
    $now = time();
    $seminarEnd = $seminarStart + (60 * 60); // seminars last 60 mins

    if ($now >= $seminarStart - $buffer && $now <= $seminarEnd + $buffer) {
      $isLive = true;
    }

    return $isLive;
  }

  public function __get ($name) {
    return $this->_data[$name];
  }

  public function __set ($name, $value) {
    $this->_data[$name] = $value;
  }
}
