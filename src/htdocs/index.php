<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$year = safeParam('year');

if (!isset($TEMPLATE)) {
  $TITLE = 'Earthquake Science Center Seminars';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="'. $MOUNT_PATH . '/css/index.css" />';
  $FOOT = '';

  include 'template.inc.php';
}

$db = new Db();

// $year is NULL when viewing default page (upcoming seminars)
$rsSeminars = $db->querySeminars($year);

$seminarsHtml = '<ul>';
while ($row = $rsSeminars->fetch(PDO::FETCH_OBJ)) {
  $seminarsHtml .= sprintf('<li>%s</li>',
    $row->topic
  );
}
$seminarsHtml .= '</ul>';

?>

<?php print $seminarsHtml; ?>
