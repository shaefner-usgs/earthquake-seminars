<?php

include_once '../conf/config.inc.php'; // app config

/**
 * Seminars list view
 * - creates the HTML for index.php
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class SeminarListView {
  private $_collection;
  private $_currentYear;
  private $_year;

  public function __construct (SeminarCollection $collection, $year) {
    $this->_collection = $collection;
    $this->_currentYear = date('Y');
    $this->_year = $year;
  }

  private function _getDescription () {
    return '<p>Seminars typically take place at <strong>10:30 AM
      Wednesdays</strong> in the <strong>Rambo Auditorium</strong> (main USGS
      Conference Room). The USGS Campus is located at
      <a href="/contactus/menlo/menloloc.php" title="Campus Map and
      Directions">345 Middlefield Road, Menlo Park, CA</a>.</p>
      <p>We record most seminars. You can watch live or
      <a href="' . $GLOBALS['MOUNT_PATH'] . "/archives/$this->_currentYear" .
      '">check the archives</a> to view a past seminar.</p>';
  }

  private function _getPodcasts () {
    return '<h3>Video Podcast</h3>
      <ul class="feeds no-style">
        <li class="itunes">
          <a href="http://itunes.apple.com/us/podcast/usgs-earthquake-science-center/id413770595">
            iTunes
          </a>
        </li>
        <li class="xml">
          <a href="' . $GLOBALS['MOUNT_PATH'] . '/podcast.xml">
            XML (Atom)
          </a>
        </li>
      </ul>';
  }

  // Create HTML for seminars list
  private function _getSeminarList () {
    if (!$this->_collection->seminars) {
      $seminarListHtml = '<p class="alert info">No Seminars Found</p>';
    } else {
      $prevMonth = NULL;
      $seminarListHtml = '';

      foreach ($this->_collection->seminars as $seminar) {

        // Flag upcoming seminars that aren't on the "regular" day/time
        if ($seminar->day !== 'Wednesday' && !$this->_year) {
          $seminar->dateShort = "<mark>$seminar->dateShort</mark>";
        }
        if ($seminar->time !== '10:30 AM' && !$this->_year) {
          $seminar->time = "<mark>$seminar->time</mark>";
        }

        // Show month & year header; open/close <ul>'s
        if ($seminar->month !== $prevMonth) {
          if ($prevMonth) {
            $seminarListHtml .= '</ul>';
          }
          $seminarListHtml .= "<h2>$seminar->month $seminar->year</h2>";
          $seminarListHtml .= '<ul class="seminars no-style' . $cssClass . '">';
        }

        // speaker field will be empty if there's no seminar
        // (committee likes to post "no seminar" messages)
        if ($seminar->speaker) {
          $seminar->openTag = '<a href="' . $GLOBALS['MOUNT_PATH'] . '/' .
            $seminar->ID . '">';
          $seminar->closeTag = '</a>';
        } else {
          $seminar->openTag = '<div>';
          $seminar->closeTag = '</div>';
        }

        $seminarListHtml .= sprintf('<li>
            %s
              <div class="topic">
                <h3>%s</h3>
                <p>%s</p>
              </div>
              <time datetime="%s">
                %s <span class="time">%s</span>
              </time>
            %s
          </li>',
          $seminar->openTag,
          $seminar->topic,
          $seminar->speaker,
          date('c', $seminar->timestamp),
          $seminar->dateShort,
          $seminar->time,
          $seminar->closeTag
        );

        $prevMonth = $seminar->month;
      }
      $seminarListHtml .= '</ul>';
    }

    return $seminarListHtml;
  }

  public function render () {
    print $this->_getDescription();
    print $this->_getPodcasts();
    print $this->_getSeminarList();
  }
}
