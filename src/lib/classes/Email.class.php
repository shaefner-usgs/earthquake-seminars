<?php

/**
 * Create and send the seminar email reminders.
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 *
 * @param $options {Array}
 *   [
 *      'data': {Array} // key-value pairs for populating email template
 *      'from': {String}
 *      'subject': {String}
 *      'template': {String} // full path to template file
 *      'to': {String}
 *   ]
 */
class Email {
  private $_data,
          $_from,
          $_message,
          $_subject,
          $_template,
          $_to;

  public function __construct($options) {
    $this->_data = $options['data'];
    $this->_from = $options['from'];
    $this->_subject = $options['subject'];
    $this->_template = $options['template'];
    $this->_to = $options['to'];

    $this->_create();
  }

  /**
   * Create the email message body.
   */
  private function _create() {
    $this->_message = file_get_contents($this->_template);

    // Substitute seminar data for mustache placeholders
    foreach ($this->_data as $key => $value) {
      $pattern = '{{' . $key . '}}';
      $this->_message = str_replace($pattern, $value, $this->_message);
    }

    // Insert line breaks to avoid mailservers' 990-character limit
    $this->_message = wordwrap($this->_message, 80, "\n", false);
  }

  /**
   * Send the email.
   */
  public function send() {
    $headers = [
      'From: ' . $this->_from,
      'MIME-Version: 1.0',
      'Content-type: text/html; charset=UTF-8'
    ];

    mail(
      $this->_to,
      $this->_subject,
      $this->_message,
      implode("\r\n", $headers)
    );
  }
}
