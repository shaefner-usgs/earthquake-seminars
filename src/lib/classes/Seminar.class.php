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

      $this->_addFields($datetime);
    }
  }

  public function __get ($name) {
    return $this->_data[$name];
  }

  public function __set ($name, $value) {
    if (preg_match('/^\d+$/', $value)) {
      $value = intVal($value);
    }
    $this->_data[$name] = $value;
  }

  /**
   * Add affiliation to speaker name in a new field
   */
  private function _addAffiliation () {
    $this->_data['speakerWithAffiliation'] = $this->_data['speaker'];

    if ($this->_data['affiliation']) {
      $this->_data['speakerWithAffiliation'] .= ', ' . $this->_data['affiliation'];
    }
  }

  /**
   * Add additional fields (not in seminars_list table) to model
   *
   * @param $datetime {String}
   */
  private function _addFields ($datetime) {
    $timestamp = strtotime($datetime);
    $year = date('Y', $timestamp);

    $videoDomain = 'https://escweb.wr.usgs.gov';
    $videoFile = str_replace('-', '', substr($datetime, 0, 10)) . '.mp4';
    $videoPath = "/content/contactus/menlo/seminars/$year";
    $videoSrc = $videoDomain . $videoPath . '/' . $videoFile;

    $this->_data['date'] = date('F j, Y', $timestamp);
    $this->_data['day'] = date('l', $timestamp);
    $this->_data['dayDate'] = date('l, F jS', $timestamp);
    $this->_data['dayDateShort'] = date('D, M j', $timestamp);
    $this->_data['month'] = date('F', $timestamp);
    $this->_data['noSeminar'] = $this->_getNoSeminar();
    $this->_data['status'] = $this->_getStatus($timestamp);
    $this->_data['time'] = date('g:i A', $timestamp);
    $this->_data['timestamp'] = $timestamp;
    $this->_data['videoPlaylist'] = str_replace('mp4', 'xml', $videoSrc);
    $this->_data['videoSrc'] = $videoSrc;
    $this->_data['videoTrack'] = str_replace('mp4', 'vtt', $videoSrc);
    $this->_data['year'] = $year;

    $this->_addAffiliation();
  }

  /**
   * Get flag to filter out 'no seminar' postings from archives
   *
   * @return $noSeminar {Boolean}
   */
  private function _getNoSeminar () {
    $noSeminar = false;

    if (!$this->_data['speaker']) {
      $noSeminar = true;
    }

    return $noSeminar;
  }

  /**
   * Get seminar status: past, today, live, or future
   *
   * @param $timestamp {Int}
   *
   * @return $status {String}
   */
  private function _getStatus ($timestamp) {
    $status = 'past'; // default value

    if ($this->_seminarDate === $this->_todaysDate) {
      if (time() < $timestamp) {
        $status = 'today';
      }
      if ($this->_isLive($timestamp)) {
        $status = 'live';
      }
    } else if ($this->_seminarDate > $this->_todaysDate) {
      $status = 'future';
    }

    return $status;
  }

  /**
   * Check if seminar is live now
   *
   * @param $seminarStart {Int}
   *     timestamp of seminar begin time
   *
   * @return $isLive {Boolean}
   */
  private function _isLive ($seminarStart) {
    $buffer = 5 * 60; // 5 mins
    $isLive = false; // default value
    $now = time();
    $seminarEnd = $seminarStart + (60 * 60); // seminars last 60 mins

    if ($now >= $seminarStart - $buffer && $now <= $seminarEnd + $buffer) {
      $isLive = true;
    }

    return $isLive;
  }
}
