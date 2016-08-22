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

  public function __construct (SeminarCollection $collection) {
    $this->_collection = $collection;
  }

  // Create HTML for seminars list
  private function _getSeminarList () {
    if (!$this->_collection->seminars) {
      $seminarListHtml = '<p class="alert info">No Seminars Found</p>';
    } else {
      $prevMonth = NULL;
      $seminarListHtml = '';

      foreach ($this->_collection->seminars as $seminar) {
        $href = $GLOBALS['MOUNT_PATH'] . '/' . $seminar->ID;
        $livenow = '';

        // Flag upcoming seminars that aren't on the "regular" day/time
        if ($seminar->category === 'upcoming' && $seminar->day !== 'Wednesday') {
          $seminar->dateShort = "<mark>$seminar->dateShort</mark>";
        }
        if ($seminar->category === 'upcoming' && $seminar->time !== '10:30 AM') {
          $seminar->time = "<mark>$seminar->time</mark>";
        }

        // Show month & year headers; open/close <ul>'s
        if ($seminar->month !== $prevMonth) {
          if ($prevMonth) {
            $seminarListHtml .= '</ul>';
          }
          $seminarListHtml .= "<h2>$seminar->month $seminar->year</h2>";
          $seminarListHtml .= '<ul class="' . $seminar->category . ' seminars no-style">';
        }

        // speaker field will be empty if there's no seminar
        // (committee likes to post "no seminar" messages)
        if ($seminar->speaker) {
          $openTag = '<a href="' . $href . '">';
          $closeTag = '</a>';

          // show "Live now" button
          if ($seminar->video === 'yes' && $seminar->status === 'live') {
            $livenow = '<div class="livenow">
                <button class="green">Live now</button>
              </div>';
          }
        } else {
          $openTag = '<div>';
          $closeTag = '</div>';
        }

        $seminarListHtml .= sprintf('<li class="%s">
            %s
              <div class="topic">
                <h3>%s</h3>
                <p>%s</p>
              </div>
              <time datetime="%s">
                %s <span class="time">%s</span>
              </time>
              %s
            %s
          </li>',
          $seminar->status,
          $openTag,
          $seminar->topic,
          $seminar->speaker,
          date('c', $seminar->timestamp),
          $seminar->dateShort,
          $seminar->time,
          $livenow,
          $closeTag
        );

        $prevMonth = $seminar->month;
      }
      $seminarListHtml .= '</ul>';
    }

    return $seminarListHtml;
  }

  public function render () {
    print $this->_getSeminarList();
  }
}
