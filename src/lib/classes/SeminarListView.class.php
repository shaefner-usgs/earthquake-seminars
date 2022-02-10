<?php

/**
 * Create the HTML for the given collection of seminars.
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 *
 * @param $collection {Object}
 */
class SeminarListView {
  private $_collection;

  public function __construct (SeminarCollection $collection) {
    $this->_collection = $collection;
  }

  /**
   * Create the HTML for the view.
   *
   * @return $html {String}
   */
  private function _create () {
    if ($this->_collection->seminars) {
      $html = '';
      $prevMonth = NULL;

      foreach ($this->_collection->seminars as $seminar) {
        if ($seminar->noSeminar && $seminar->status === 'past') {
          continue; // skip 'No Seminar' entries in archives list
        }

        // Show month & year headers; close <ul>s
        if ($seminar->month !== $prevMonth) {
          if ($prevMonth) {
            $html .= '</ul>';
          }

          $html .= "<h2>$seminar->month $seminar->year</h2>";
          $html .= '<ul class="list no-style">';
        }

        $html .= $this->_getItem($seminar);
        $prevMonth = $seminar->month;
      }

      $html .= '</ul>';
    } else {
      $html = '<p class="alert info">No seminars match the selected time period.</p>';
    }

    return $html;
  }

  /**
   * Flag a future seminar if it isn't on the "regular" day/time.
   *
   * @param $seminar {Object}
   */
  private function _flagSeminar ($seminar) {
    $seminar->date = date('D, M j', $seminar->timestamp); // display date

    if ($seminar->weekday !== 'Wednesday' && $seminar->status === 'future') {
      $seminar->date = "<mark>$seminar->date</mark>";
    }

    if ($seminar->time !== '10:30 AM' &&
      ($seminar->status === 'today' || $seminar->status === 'future')
    ) {
      $seminar->time = "<mark>$seminar->time</mark>";
    }
  }

  /**
   * Get the HTML for the "Live" button if the given seminar is today.
   *
   * @param $seminar {Object}
   *
   * @return $html {String}
   */
  private function _getButton ($seminar) {
    $html = '';

    if ($seminar->video) {
      if ($seminar->status === 'live') {
        $html = '
          <div class="status">
            <button class="red">Live now</button>
          </div>';
      } else if ($seminar->status === 'today') {
        $html = '
          <div class="status">
            <button class="green">Live today</button>
          </div>';
      }
    }

    return $html;
  }

  /**
   * Get the HTML for a seminar list item.
   *
   * @param $seminar {Object}
   *
   * @return {String}
   */
  private function _getItem ($seminar) {
    global $MOUNT_PATH;

    $button = $this->_getButton($seminar);
    $href = "$MOUNT_PATH/$seminar->ID";

    if ($seminar->noSeminar) {
      $closeTag = '</div>';
      $openTag = '<div>';
    } else {
      $closeTag = '</a>';
      $openTag = '<a href="' . $href . '">';

      $this->_flagSeminar($seminar);
    }

    return sprintf('
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
      $seminar->date,
      $seminar->time,
      $seminar->imageSrc,
      $button,
      $closeTag
    );
  }

  /**
   * Render the view.
   */
  public function render () {
    print $this->_create();
  }
}
