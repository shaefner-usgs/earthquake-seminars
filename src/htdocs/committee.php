<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/classes/Db.class.php'; // db connector, queries
include_once '_feeds.inc.php'; // sets $feedsHtml

if (!isset($TEMPLATE)) {
  $TITLE = 'Seminar Committee';
  $TITLETAG = $TITLE . ' | Earthquake Science Center Seminars';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="'. $MOUNT_PATH . '/css/committee.css" />';
  $FOOT = '';

  include 'template.inc.php';
}

$db = new Db();
$listHtml = '<ul>';
$prevYear = NULL;
$rsCommittee = $db->queryCommittee('all');
$tableHtml = '<table>';

while ($member = $rsCommittee->fetch(PDO::FETCH_OBJ)) {
  $phone = '';
  $year = 'Current';

  if (preg_match("/committee-(\d{4})/", $member->role, $matches)) {
    $year = $matches[1]; // get year from role column
  }

  if ($member->phone) {
    $phone = ", $member->phone";
  }

  // Current committee is listed separately from past members
  if ($year === 'Current') {
    $listHtml .= sprintf('<li><a href="mailto:%s">%s</a>%s</li>',
      $member->email,
      $member->name,
      $phone
    );
  } else { // past committee members
    if ($year !== $prevYear) {
      // Add committee members and close tags on previous row
      if (isset($prevYear)) {
        $tableHtml .= implode(', ', $members);
        $tableHtml .= '</td></tr>';
      }

      // Start a new row
      $members = [];
      $tableHtml .= "\n<tr><th>$year</th><td>";
    }

    array_push($members, $member->name);

    $prevYear = $year;
  }
}

// Finish final row
$tableHtml .= implode(', ', $members);

// Close tags
$listHtml .= '</ul>';
$tableHtml .= '</td></tr></table>';

?>

<p>Please contact the seminar committee to suggest a future topic or speaker:</p>

<?php print $listHtml; ?>

<h2>Past Seminar Committees</h2>

<?php

print $tableHtml;
print $feedsHtml;
