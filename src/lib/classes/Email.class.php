<?php

/**
 * Create and send an email
 *
 * @param $options {Array}
 *   [
 *      'data': {Array} // key-value pairs for populating email template
 *      'from': {String}
 *      'template': {String} // full path to template file
 *      'subject': {String}
 *      'to': {String}
 *   ]
 */
class Email {
  private $_data, $_from, $_message, $_template, $_subject, $_to;

  public function __construct($options) {
    $this->_data = $options['data'];
    $this->_from = $options['from'];
    $this->_template = $options['template'];
    $this->_subject = $options['subject'];
    $this->_to = $options['to'];

    $this->_message = $this->_getTemplate();

    $this->_create(); // Create the message
  }

  /**
   * Create email message body
   */
  private function _create() {
    // Substitute seminar data for mustache placeholders
    foreach ($this->_data as $key => $value) {
      $pattern = '{{' . $key . '}}';
      $this->_message = str_replace($pattern, $value, $this->_message);
    }

    // Insert line breaks to avoid mailservers' 990-character limit
    $this->_message = wordwrap($this->_message, 80, "\n", false);
  }

  /**
   * Read email template into a string and return it
   *
   * @return {String}
   */
  private function _getTemplate() {
    return file_get_contents($this->_template);
  }

  /**
   * Send email
   */
  public function send() {
    $headers = [
      'From: ' . $this->_from,
      'MIME-Version: 1.0',
      'Content-type: text/html; charset=iso-8859-1'
    ];

    mail($this->_to, $this->_subject, $this->_message, implode("\r\n", $headers));
  }
}
