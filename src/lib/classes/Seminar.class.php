<?php

/**
 * Model for ESC Seminar
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Seminar {
  private $_buffer,
    $_data = [],
    $_endTime,
    $_now,
    $_seminarDate,
    $_startTime,
    $_todaysDate;

  public function __construct () {
    if ($this->_data['datetime']) {
      $this->_startTime = strtotime($this->_data['datetime']);
      $this->_buffer = 5 * 60; // 5 mins
      $this->_endTime = $this->_startTime + (60 * 60); // add 60 mins
      $this->_now = time();
      $this->_seminarDate = date('Y-m-d', $this->_startTime);
      $this->_todaysDate = date('Y-m-d');

      $this->_addFields();
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
   */
  private function _addFields () {
    global $MOUNT_PATH;

    $year = date('Y', $this->_startTime);
    $image = $this->_getImage();
    $videoFile = str_replace('-', '', $this->_seminarDate) . '.mp4';
    $videoSrc = "$MOUNT_PATH/data/$year/$videoFile";

    $this->_data['date'] = date('F j, Y', $this->_startTime);
    $this->_data['day'] = date('l', $this->_startTime);
    $this->_data['dayDate'] = date('l, F jS', $this->_startTime);
    $this->_data['dayDateShort'] = date('D, M j', $this->_startTime);
    $this->_data['imageType'] = $image['type'];
    $this->_data['imageUri'] = $image['uri'];
    $this->_data['imageUrl'] = $image['url'];
    $this->_data['imageWidth'] = $image['width'];
    $this->_data['month'] = date('F', $this->_startTime);
    $this->_data['noSeminar'] = $this->_getNoSeminar();
    $this->_data['status'] = $this->_getStatus($this->_startTime);
    $this->_data['time'] = date('g:i A', $this->_startTime);
    $this->_data['timestamp'] = $this->_startTime;
    $this->_data['videoFile'] = $videoFile;
    $this->_data['videoPlaylist'] = str_replace('mp4', 'xml', $videoSrc);
    $this->_data['videoSrc'] = $videoSrc;
    $this->_data['videoTrack'] = str_replace('mp4', 'vtt', $videoSrc);
    $this->_data['year'] = $year;

    $this->_addAffiliation();
  }

  /**
   * Get attributes of uploaded image (or 'default' podcast image if none)
   *
   * @return $image {Array}
   */
  private function _getImage () {
    global $DATA_DIR, $MOUNT_PATH;

    $image = [
      'width' => 300 // default
    ];
    $path = "$DATA_DIR/images/" . $this->_data['image'];

    if ($this->_data['image'] && is_file($path)) {
      $image['type'] = 'upload';
      $image['uri'] = "$MOUNT_PATH/data/images/" . $this->_data['image'];

      // Set width of image so it displays at 300px in max dimension
      list($width, $height) = getimagesize($path);
      if ($height > $width) {
        $image['width'] = 300 * $width / $height;
      }
    } else {
      $image['type'] = 'default';
      $image['uri'] = "$MOUNT_PATH/img/podcast-small.png";
    }

    $image['url'] = 'https://earthquake.usgs.gov' . $image['uri'];

    return $image;
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
   * Get seminar status: past, today( after), live, or future
   *
   * @return $status {String}
   */
  private function _getStatus () {
    $status = 'past'; // default

    if ($this->_seminarDate === $this->_todaysDate) {
      $status = 'today';

      if ($this->_isAfter()) {
        $status .= ' after'; // signal that seminar is over
      } else if ($this->_isLive()) {
        $status = 'live';
      }
    } else if ($this->_seminarDate > $this->_todaysDate) {
      $status = 'future';
    }

    return $status;
  }

  /**
   * Check if seminar is over
   *
   * @return $isAfter {Boolean}
   */
  private function _isAfter () {
    $isAfter = false;

    if ($this->_now > $this->_endTime + $this->_buffer) {
      $isAfter = true;
    }

    return $isAfter;
  }

  /**
   * Check if seminar is live now
   *
   * @return $isLive {Boolean}
   */
  private function _isLive () {
    $isLive = false;

    if ($this->_now >= $this->_startTime - $this->_buffer &&
      $this->_now <= $this->_endTime + $this->_buffer
    ) {
      $isLive = true;
    }

    return $isLive;
  }
}
