<?php

include_once __DIR__ . '/_autop.inc.php';
include_once __DIR__ . '/_getEntities.inc.php';

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
 * @return {Boolean}
 *    returns true if found, otherwise nada
 */
function remoteFileExists ($url) {
  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_exec($ch);
  $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($retcode === 200) {
    return true;
  }
}
