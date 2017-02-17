<?php

// Don't cache
date_default_timezone_set('America/Los_Angeles');
$date = date('D, d M Y H:i:s T');
header("Expires: " . $date);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header('Content-Type: application/json');

// Geolocation API and data file
include('/home/www/vhosts/earthquake/htdocs/template/widgets/iplocator/geoipcity.inc.php');
include('/home/www/vhosts/earthquake/htdocs/template/widgets/iplocator/geoipregionvars.php');
$iplocator = geoip_open('/home/www/vhosts/earthquake/htdocs/template/widgets/iplocator/GeoLiteCity.dat', GEOIP_MEMORY_CACHE);

// Get stream details from XML file on video server
$xmlstr = file_get_contents('http://video2.wr.usgs.gov:8086/serverinfo/');
//$xmlstr = file_get_contents('http://video2.wr.usgs.gov:8086/connectioncounts');
//$xmlstr = file_get_contents('http://video2.wr.usgs.gov:8086/connectioncounts?flat');
//$xmlstr = file_get_contents('stream.xml');
//$xmlstr = file_get_contents('stream-off.xml');
$xml = new SimpleXMLElement($xmlstr);

if ($xml) {
  $metadata = array();
  $features = array();
  $metadata['status'] = 'inactive'; // default value

  foreach($xml->VHost[0]->Application as $application) {

    if ($application->Name == 'live') { // only look at connections that are watching 'live' stream
      $metadata['duration'] = (String) $application->TimeRunning;
      $metadata['date'] = $date;
      $metadata['current'] = (String) $application->ConnectionsCurrent;
      $metadata['total'] = (String) $application->ConnectionsTotal;

      // check if stream is currently running
      // sometimes stream is 'loaded' even when it's not actively streaming
      if ((String) $application->Status === 'loaded') {
        $metadata['status'] = 'loaded';
      }
      // only set to 'active' is there are active connections (wirecast itself is considered an active connection)
      if ((Int) $application->ConnectionsCurrent > 0) {
        $metadata['status'] = 'active';
      }

      // Desktop users (Flash)
      if ($application->ApplicationInstance->Client) { // suppress warning if no clients connected
        foreach($application->ApplicationInstance->Client as $client) { // loop thru client connections

          if (preg_match("/Wirecast/", $client->FlashVersion)) {
            $type = 'wirecast'; // wirecast client
          } else {
            $type = 'desktop';
          }

          $feature = buildUserJson($client, $type);
          array_push($features, $feature);
        }
      }

      // Mobile users (iPhone, Android)
      if ($application->ApplicationInstance->HTTPSession) {
        foreach($application->ApplicationInstance->HTTPSession as $httpsession) {
          $type = 'mobile';
          $feature = buildUserJson($httpsession, $type);
          array_push($features, $feature);
        }
      }

    }
  }

  $json_array = array(
    'type' => 'FeatureCollection',
    'metadata' => $metadata,
    'features' => $features
  );

  print json_encode($json_array);
}

geoip_close($iplocator);


function buildUserJson($user, $type) {

  // try to get host name from IP address
  $name = gethost ($user->IpAddress);

  // try to get lat, lng from IP address
  $geo = geoip_record_by_addr($GLOBALS['iplocator'], $user->IpAddress);
  if ($geo) {
    $feature['geometry'] = array(
      'type' => 'Point',
      'coordinates' => array($geo->longitude, $geo->latitude)
    );
  }

  $feature['type'] = 'Feature';
  $feature['id'] = count($GLOBALS['features']) + 1;

  $feature['properties'] = array(
    'ip' => (String) $user->IpAddress,
    'name' => $name,
    'start' => (String) $user->DateStarted,
    'duration' => (String) $user->TimeRunning,
    'version' => (String) $user->FlashVersion,
    'referrer' => (String) $user->Referrer,
    'type' => $type
  );

  return $feature;
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
