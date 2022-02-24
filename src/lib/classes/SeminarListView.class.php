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
   * @param $year {String}
   *
   * @return $html {String}
   */
  private function _create ($year) {
    if ($this->_collection->seminars) {
      $html = $this->_getDetails();
      $prevMonth = NULL;

      foreach ($this->_collection->seminars as $seminar) {
        if ( // past 'No Seminar' entry or today's seminar w/ video not yet posted
          ($seminar->noSeminar && $seminar->status === 'past') ||
          ($year && preg_match('/today/', $seminar->status) && !$seminar->videoSrc)
        ) {
          continue; // skip this seminar in Archives list
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
        $html = '<button class="red">Live now</button>';
      } else if ($seminar->status === 'today') {
        $html = '<button class="green">Live today</button>';
      }
    }

    return $html;
  }

  /**
   * Get the seminar details list header.
   *
   * @return {String}
   */
  private function _getDetails () {
    global $MOUNT_PATH, $currentYear; // year is set in _navigation.inc.php

    return sprintf('
      <div class="row details">
        <div class="column one-of-five">
          <img src="%s/img/podcast-small.png" alt="podcast icon" />
        </div>
        <div class="column four-of-five">
          <p>Seminars typically take place virtually at <strong>10:30 AM</strong>
            (Pacific) on <strong>Wednesdays</strong> on Microsoft Teams.<!--in
            the <strong>Yosemite Conference Room</strong> (Rm 2030A, Bldg 19).
            The USGS Campus is located at <a href="/contactus/menlo/menloloc.php"
            title="Campus`Map and Directions">350 North Akron Road, Moffett
            Field, CA</a>.--></p>
          <p>We record most seminars. You can watch live or
            <a href="%s/archives/%s">check the archives</a> to view a past
            seminar.</p>
        </div>
      </div>',
      $MOUNT_PATH,
      $MOUNT_PATH,
      $currentYear
    );
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
    $seminar->date = date('D, M j', $seminar->timestamp); // display date

    if ($button) {
      $seminar->date = $button;
    } else if (preg_match('/today/', $seminar->status)) {
      $seminar->date = 'Today';
    }

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
      </li>',
      $seminar->status,
      $openTag,
      $seminar->topic,
      $seminar->speakerWithAffiliation,
      date('c', $seminar->timestamp),
      $seminar->date,
      $seminar->time,
      $seminar->imageSrc,
      $closeTag
    );
  }

  /**
   * Render the view.
   *
   * @param $year {String}
   */
  public function render ($year) {
    print $this->_create($year);
  }
}
