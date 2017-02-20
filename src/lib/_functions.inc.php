<?php

include_once('_autop.inc.php');
include_once('_getEntities.inc.php');

/**
 * Get a request parameter from $_GET or $_POST
 *
 * @param $name {String}
 *     The parameter name
 * @param $default {?} default is NULL
 *     Optional default value if the parameter was not provided.
 * @param $filter {PHP Sanitize filter} default is FILTER_SANITIZE_STRING
 *     Optional sanitizing filter to apply
 *
 * @return $value {String}
 */
function safeParam ($name, $default=NULL, $filter=FILTER_SANITIZE_STRING) {
  $value = NULL;

  if (isset($_POST[$name]) && $_POST[$name] !== '') {
    $value = filter_input(INPUT_POST, $name, $filter);
  } else if (isset($_GET[$name]) && $_GET[$name] !== '') {
    $value = filter_input(INPUT_GET, $name, $filter);
  } else {
    $value = $default;
  }

  return $value;
}

/**
 * Check if a file exists on a remote server
 *
 * @param $url {String}
 *    The remote URL to check
 *
 * @return $size {Int}
 *    The size of the remote file (returns 0 if not found)
 */
function remoteFileExists ($url) {
  $size = 0;

  $urlComponents = parse_url($url);
  $host = $urlComponents['host'];
  $fp = fsockopen('ssl://' . $host, 443, $errno, $errstr, 30);

  if ($fp) {
    $out = "GET $url HTTP/1.1\r\n"; // HEAD vs GET ??
    $out .= "Host: $host\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);

    $needle = 'Content-Length: ';
    while (!feof($fp)) {
      $headers = fgets ($fp, 128);
      if (preg_match("/$needle/i", $headers)) {
        $size = trim(substr($headers, strlen($needle)));
        break;
      }
    }
    fclose ($fp);
  }

  return $size;
}
