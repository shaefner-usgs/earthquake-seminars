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

  private function _getSeminar () {
    if (!$this->_model->ID) {
      $seminarHtml = '<p class="alert error">ERROR: Seminar Not Found</p>';
    } else {
      $captions = '';
      if ($this->_model->status !== 'future') {
        $captions = '<p class="captions">Closed captions are usually available a
          few days after the seminar. To turn them on, press the &lsquo;CC&rsquo;
          button on the video player. For older seminars that don&rsquo;t have
          closed captions, please <a href="mailto:shaefner@usgs.gov">email
          us</a>, and we will do our best to accommodate your request.</p>';
      }
      $host = '';
      if ($this->_model->host) {
        $host = '<dt>Host:</dt><dd>' . $this->_model->host . '</dd>';
      }
      $flash = '';
      if ($this->_model->status === 'live') {
        $flash = '<p class="flash"><a href="http://get.adobe.com/flashplayer/">Adobe
          Flash Player</a> is <strong>required</strong> to view live webcasts.</p>';
      }
      $summary = '';
      if ($this->_model->summary) {
        $summary = autop($this->_model->summary); // add <p> tag(s) to summary
      }
      $video = $this->_getVideo();

      $seminarHtml = sprintf('
        <h2>%s</h2>
        %s
        <div class="row %s %s">
          <div class="column two-of-three video">
            %s
          </div>
          <div class="column one-of-three details">
            <h4>%s</h4>
            <p>%s <span class="time">at %s</p>
            <dl>
              <dt class="location">Location:</dt>
              <dd class="location">%s</dd>
              %s
            </dl>
            %s
          </div>
        </div>
        %s',
        $this->_model->topic,
        $summary,
        $this->_model->category,
        $this->_model->status,
        $video,
        $this->_model->speaker,
        $this->_model->date,
        $this->_model->time,
        $this->_model->location,
        $host,
        $flash,
        $captions
      );
    }

    return $seminarHtml;
  }

  private function _getVideo () {
    $video = '';

    if ($this->_model->video === 'yes') {
      if ($this->_model->status === 'past') { // recorded video
        if ($this->_remoteFileExists($this->_model->videoSrc)) { // mp4 file
          $video = $this->_getVideoTag($this->_model->status);
        } else {
          $video = '<h3>Video not found</h3>
            <p>Please try back later. Videos are usually posted within a few hours.</p>';
        }
      }
      else if ($this->_model->status === 'today') { // seminar later today
        $video = '<h3>This seminar will be webcast live today</h3>
        <p>Please reload this page at ' . $this->_model->time . ' Pacific.</p>';
      }
      else if ($this->_model->status === 'live') { // live stream
        $video = $this->_getVideoTag($this->_model->status);
        $video .= '<p><a href="http://video2.wr.usgs.gov:1935/live/mplive/playlist.m3u8">
        View on a mobile device</a></p>';
      }
    } else {
      $video = '<h3>Video not available</h3>
        <p>This seminar was not recorded or is not available to view online.</p>';
    }

    return $video;
  }

  private function _getVideoTag ($type) {
    if ($type === 'past') {
      $videoTag = '<video src="' . $this->_model->videoSrc . '" width="100%"
        crossorigin="anonymous" controls="controls">';

      if ($this->_remoteFileExists($this->_model->videoTrack)) { // vtt file
        $videoTag .= '<track label="English" kind="captions"
          src="' . $this->_model->videoTrack . '" default="default" />';
      }

      $videoTag .= '</video>';
    }
    else if ($type === 'live') {
      $videoTag = '<video src="mplive?streamer=rtmp://video2.wr.usgs.gov/live"
        width="100%" controls="controls"></video>';
    }

    return $videoTag;
  }

  private function _remoteFileExists ($url) {
    $size = 0;

    $urlComponents = parse_url($url);
    $host = $urlComponents['host'];
    $fp = fsockopen($host, 80, $errno, $errstr, 5);

    if (!$fp) {
      return false;
    } else {
      $out = "GET $url HTTP/1.1\r\n"; // HEAD vs GET ??
      $out .= "Host: $host\r\n";
      $out .= "Connection: Close\r\n\r\n";
      fwrite($fp, $out);

      $needle = 'Content-Length: ';
      while (!feof($fp)) {
        $header = fgets ($fp, 128);
        if (preg_match("/$needle/i", $header)) {
          $size = trim(substr($header, strlen($needle)));
          break;
        }
      }
      fclose ($fp);
    }

    return $size;
  }

  public function render () {
    print $this->_getSeminar();
  }
}
