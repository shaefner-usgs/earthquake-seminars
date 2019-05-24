<?php

include_once __DIR__ . '/../../conf/config.inc.php'; // app config

/**
 * Seminars list view - creates the HTML for seminar index page
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class SeminarListView {
  private $_collection;

  public function __construct (SeminarCollection $collection) {
    $this->_collection = $collection;
  }

  /**
   * Create <li> tag for each seminar in list
   *
   * @param $seminar {Object}
   *
   * @return $liTag {String}
   */
  private function _getLiTag ($seminar) {
    $href = $GLOBALS['MOUNT_PATH'] . '/' . $seminar->ID;
    $live = '';

    // speaker field will be empty if there's no seminar ("no seminar" notices)
    if ($seminar->speaker) {
      $openTag = '<a href="' . $href . '">';
      $closeTag = '</a>';

      // show "Live" button
      if ($seminar->video === 'yes' && $seminar->status === 'live') {
        $live = '<div class="live">
            <button class="red">Live now</button>
          </div>';
      } else if ($seminar->video === 'yes' && $seminar->status === 'today') {
        $live = '<div class="live">
            <button class="green">Live today</button>
          </div>';
      }
    } else {
      $openTag = '<div>';
      $closeTag = '</div>';
    }

    $liTag .= sprintf('<li class="%s">
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
      $live,
      $closeTag
    );

    return $liTag;
  }

  /**
   * Create HTML for seminars list
   *
   * @return $seminarListHtml {String}
   */
  private function _getSeminarList () {
    if (!$this->_collection->seminars) {
      $seminarListHtml = '<p class="alert info">No Seminars Found</p>';
    } else {
      $prevMonth = NULL;
      $seminarListHtml = '';

      foreach ($this->_collection->seminars as $seminar) {
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
        $prevMonth = $seminar->month;

        // Get <li> with seminar details
        $seminarListHtml .= $this->_getLiTag($seminar);
      }
      $seminarListHtml .= '</ul>';
    }

    return $seminarListHtml;
  }

  public function render () {
    print $this->_getSeminarList();
  }
}
