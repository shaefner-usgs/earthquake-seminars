<?php

/**
 * ESC seminar model.
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Seminar {
  private $_buffer,
          $_data = [],
          $_endTime,
          $_now,
          $_seminarDate,
          $_startTime;

  public function __construct () {
    if ($this->datetime) {
      $this->_buffer = 5 * 60; // 5 mins
      $this->_startTime = strtotime($this->datetime);
      $this->_endTime = $this->_startTime + (60 * 60); // add 60 mins
      $this->_now = time();
      $this->_seminarDate = date('Y-m-d', $this->_startTime);

      $this->_addProps();
    }
  }

  public function __get ($name) {
    if (array_key_exists($name, $this->_data)) {
      return $this->_data[$name];
    }
  }

  public function __set ($name, $value) {
    if (preg_match('/^\d+$/', $value)) {
      $value = intVal($value);
    }

    $this->_data[$name] = trim($value);
  }

  /**
   * Add additional properties to the model, which initially includes all fields
   * in the MySQL table seminars_list.
   */
  private function _addProps () {
    $basename = str_replace('-', '', $this->_seminarDate);
    $image = $this->_getImage();
    $year = date('Y', $this->_startTime);
    $playlistSrc = $this->_getSrc("$basename.xml", $year);
    $trackSrc = $this->_getSrc("$basename.vtt", $year);
    $videoSrc = $this->_getSrc("$basename.mp4", $year);

    $this->day = date('j', $this->_startTime);
    $this->dayOrdinal = date('S', $this->_startTime);
    $this->imageSrc = $image['src'];
    $this->imageType = $image['type'];
    $this->imageWidth = $image['width'];
    $this->month = date('F', $this->_startTime);
    $this->monthShort = date('M', $this->_startTime);
    $this->noSeminar = $this->_getNoSeminar();
    $this->playlistSrc = $playlistSrc;
    $this->pubDate = date('D, j M Y H:i:s T', $this->_startTime);
    $this->speakerWithAffiliation = $this->_getSpeaker();
    $this->status = $this->_getStatus($this->_startTime);
    $this->time = date('g:i A', $this->_startTime);
    $this->timestamp = $this->_startTime;
    $this->trackSrc = $trackSrc;
    $this->videoSrc = $videoSrc;
    $this->weekday = date('l', $this->_startTime);
    $this->weekdayShort = date('D', $this->_startTime);
    $this->year = $year;
  }

  /**
   * Get the attributes of the seminar's uploaded image (or the podcast image
   * if none).
   *
   * @return {Array}
   */
  private function _getImage () {
    global $DATA_DIR, $MOUNT_PATH;

    $displayWidth = 300;
    $path = "$DATA_DIR/images/" . $this->image;
    $src = "$MOUNT_PATH/img/podcast-small.png";
    $type = 'default';

    if ($this->image && file_exists($path)) {
      $src = "$MOUNT_PATH/data/images/" . $this->image;
      $type = 'upload';

      list($width, $height) = getimagesize($path);

      if ($height > $width) { // display img at 300px in max dimension
        $displayWidth = round(300 * $width / $height);
      }
    }

    return [
      'src' => $src,
      'type' => $type,
      'width' => $displayWidth
    ];
  }

  /**
   * Get a flag used to filter out 'no seminar' postings from archives.
   *
   * @return $noSeminar {Boolean}
   */
  private function _getNoSeminar () {
    $noSeminar = false;

    if (!$this->speaker) {
      $noSeminar = true;
    }

    return $noSeminar;
  }

  /**
   * Get the speaker's name (including his/her affiliation if present).
   *
   * @return speaker {String}
   */
  private function _getSpeaker () {
    $speaker = $this->speaker;

    if ($this->affiliation) {
      $speaker .= ', ' . $this->affiliation;
    }

    return $speaker;
  }

  /**
   * Get the site root-relative src to a playlist, track or video file, or an
   * empty string if the file doesn't exist.
   *
   * @param $file {String}
   * @param $year {String}
   *
   * @return $src {String}
   */
  private function _getSrc ($file, $year) {
    global $DATA_DIR, $MOUNT_PATH;

    $path = "$DATA_DIR/$year/$file";
    $src = '';

    if (file_exists($path)) {
      $src = "$MOUNT_PATH/data/$year/$file";
    }

    return $src;
  }

  /**
   * Get the seminar status: future, live, past or today.
   *
   * @return $status {String}
   */
  private function _getStatus () {
    $status = 'past'; // default
    $todaysDate = date('Y-m-d');

    if ($this->_seminarDate === $todaysDate) {
      $status = 'today';

      if ($this->_isFinished()) {
        $status .= ' past';
      } else if ($this->_isLive()) {
        $status = 'live';
      }
    } else if ($this->_seminarDate > $todaysDate) {
      $status = 'future';
    }

    return $status;
  }

  /**
   * Check if the seminar is finished.
   *
   * @return $isFinished {Boolean}
   */
  private function _isFinished () {
    $isFinished = false;

    if ($this->_now > $this->_endTime + $this->_buffer) {
      $isFinished = true;
    }

    return $isFinished;
  }

  /**
   * Check if the seminar is live now.
   *
   * @return $isLive {Boolean}
   */
  private function _isLive () {
    $end = $this->_endTime + $this->_buffer;
    $isLive = false;
    $start = $this->_startTime - $this->_buffer;

    if ($this->_now >= $start && $this->_now <= $end) {
      $isLive = true;
    }

    return $isLive;
  }
}
