<?php

include_once __DIR__ . '/_autop.inc.php';
include_once __DIR__ . '/_getEntities.inc.php';

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

/**
 * Get a request parameter from $_GET or $_POST
 *
 * @param $name {String}
 *     The parameter name
 * @param $default {?} default is NULL
 *     Optional default value if the parameter was not set.
 *
 * @return $value {String}
 */
function safeParam ($name, $default=NULL) {
  if (isset($_POST[$name]) && $_POST[$name] !== '') {
    $value = strip_tags(trim($_POST[$name]));
  } else if (isset($_GET[$name]) && $_GET[$name] !== '') {
    $value = strip_tags(trim($_GET[$name]));
  } else {
    $value = $default;
  }

  return $value;
}
