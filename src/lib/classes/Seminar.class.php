<?php

/**
 * Model for ESC Seminar
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Seminar {
  private $_data = [];

  public function __construct () {
    $datetime = $this->_data['datetime'];
    $timestamp = strtotime($datetime);

    $this->_data['dateShort'] = date('D, M j', $timestamp);
    $this->_data['dateLong'] = date('l, F j', $timestamp);
    $this->_data['month'] = date('F', $timestamp);
    $this->_data['time'] = date('g:i A', $timestamp);
    $this->_data['year'] = date('Y', $timestamp);

    $videoDomain = 'http://media.wr.usgs.gov';
    $videoPath = '/ehz/' . $this->_data['year'];
    $videoFile = str_replace('-', '', substr($datetime, 0, 10)) . '.mp4';
    $captionsFile = str_replace('mp4', 'vtt', $videoFile);

    $this->_data['video'] = $videoDomain . $videoPath . '/' . $videoFile;
    $this->_data['captions'] = $videoDomain . $videoPath . '/' . $captionsFile;
  }

  public function __get ($name) {
    return $this->_data[$name];
  }

  public function __set ($name, $value) {
    $this->_data[$name] = $value;
  }
}
