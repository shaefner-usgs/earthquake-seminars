<?php

/**
 * Model for ESC Seminar
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Seminar {
  private $_data = [];
  private $_seminarDate;
  private $_todaysDate;

  public function __construct () {
    if ($this->_data['datetime']) {
      $datetime = $this->_data['datetime'];

      $this->_seminarDate = date('Y-m-d', strtotime($datetime));
      $this->_todaysDate = date('Y-m-d');

      $this->_addAffiliation();
      $this->_addFields($datetime);
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

  // Add non-db fields to model
  private function _addFields ($datetime) {
    $timestamp = strtotime($datetime);
    $year = date('Y', $timestamp);

    $videoDomain = 'http://media.wr.usgs.gov';
    $videoFile = str_replace('-', '', substr($datetime, 0, 10)) . '.mp4';
    $videoPath = '/ehz/' . $year;
    $videoSrc = $videoDomain . $videoPath . '/' . $videoFile;

    $this->_data['date'] = date('F j, Y', $timestamp);
    $this->_data['dateShort'] = date('D, M j', $timestamp);
    $this->_data['day'] = date('l', $timestamp);
    $this->_data['month'] = date('F', $timestamp);
    $this->_data['period'] = $this->_getPeriod($timestamp);
    $this->_data['time'] = date('g:i A', $timestamp);
    $this->_data['timestamp'] = $timestamp;
    $this->_data['type'] = $this->_getType();
    $this->_data['videoSrc'] = $videoSrc;
    $this->_data['videoTrack'] = str_replace('mp4', 'vtt', $videoSrc);
    $this->_data['year'] = $year;
  }

  private function _getPeriod ($timestamp) {
    $period = 'past'; // default value
    if ($this->_seminarDate === $this->_todaysDate) {
      if (time() < $timestamp) {
        $period = 'today';
      }
      if ($this->_isLive($timestamp)) {
        $period = 'live'; // "live" trumps "today" due to buffer
      }
    } else if ($this->_seminarDate > $this->_todaysDate) {
      $period = 'future';
    }

    return $period;
  }

  private function _getType () {
    $type = 'archive'; // default value
    if ($this->_seminarDate >= $this->_todaysDate) {
      $type = 'upcoming';
    }

    return $type;
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
