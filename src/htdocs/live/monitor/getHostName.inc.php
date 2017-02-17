<?php

if (preg_match('/^[\w\.]+$/', $_GET['ip'])) {
  $ip = $_GET['ip'];

  // try to get host name from IP address
  $name = gethost ($ip);

  print $name;
}



function gethost($ip) {
  $host = exec("dig +short +time=1 -x $ip 2>&1");

  if (!$host || preg_match('/not found/', $host) || preg_match('/timed out/', $host)) {
    $r = 'unknown';
  } else {
    $r = substr($host, 0, -1);
  }
  return $r;
}

?>
