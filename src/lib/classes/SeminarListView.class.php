<?php

/**
 * Create the HTML for a list of seminars
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class SeminarListView {
  private $_collection;

  public function __construct (SeminarCollection $collection) {
    $this->_collection = $collection;
  }

  /**
   * Get <li> for a seminar
   *
   * @param $seminar {Object}
   *
   * @return $liTag {String}
   */
  private function _getLiTag ($seminar) {
    global $MOUNT_PATH;

    $href = "$MOUNT_PATH/" . $seminar->ID;
    $liTag = '';
    $status = '';

    if (!$seminar->noSeminar) {
      $openTag = '<a href="' . $href . '">';
      $closeTag = '</a>';

      // Show "Live" button if seminar is today
      if ($seminar->video && $seminar->status === 'live') {
        $status = '<div class="status">
            <button class="red">Live now</button>
          </div>';
      } else if ($seminar->video && $seminar->status === 'today') {
        $status = '<div class="status">
            <button class="green">Live today</button>
          </div>';
      }
    } else {
      $openTag = '<div>';
      $closeTag = '</div>';
    }

    $liTag .= sprintf('
      <li class="%s">
        %s
          <div class="title">
            <h3>%s</h3>
            <p>%s</p>
          </div>
          <time datetime="%s">
            %s <span class="time">%s</span>
          </time>
          <img src="%s" alt="icon" />
          %s
        %s
      </li>',
      $seminar->status,
      $openTag,
      $seminar->topic,
      $seminar->speakerWithAffiliation,
      date('c', $seminar->timestamp),
      $seminar->dayDateShort,
      $seminar->time,
      $seminar->imageUri,
      $status,
      $closeTag
    );

    return $liTag;
  }

  /**
   * Get HTML for seminars list
   *
   * @return $html {String}
   */
  private function _getList () {
    if (!$this->_collection->seminars) {
      $html = '<p class="alert info">No Upcoming Seminars</p>';
    } else {
      $prevMonth = NULL;
      $html = '';

      foreach ($this->_collection->seminars as $seminar) {
        if ($seminar->noSeminar && $seminar->status === 'past') {
          continue; // skip 'No Seminar' entries in archives list
        }
        // Flag future seminars that aren't on the "regular" day/time
        if ($seminar->day !== 'Wednesday' && $seminar->status === 'future') {
          $seminar->dayDateShort = "<mark>$seminar->dayDateShort</mark>";
        }
        if ($seminar->time !== '10:30 AM' &&
          ($seminar->status === 'today' || $seminar->status === 'future')
        ) {
          $seminar->time = "<mark>$seminar->time</mark>";
        }

        // Show month & year headers; open/close <ul>'s
        if ($seminar->month !== $prevMonth) {
          if ($prevMonth) {
            $html .= '</ul>';
          }
          $html .= "<h2>$seminar->month $seminar->year</h2>";
          $html .= '<ul class="list no-style">';
        }
        $prevMonth = $seminar->month;

        // Get <li> with seminar details
        $html .= $this->_getLiTag($seminar);
      }
      $html .= '</ul>';
    }

    return $html;
  }

  public function render () {
    print $this->_getList();
  }
}
