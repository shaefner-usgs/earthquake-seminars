<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '_feeds.inc.php'; // sets $feeds

if (!isset($TEMPLATE)) {
  $TITLE = 'Seminar Committee';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="'. $MOUNT_PATH . '/css/committee.css" />';
  $FOOT = '';

  include 'template.inc.php';
}

$db = new Db();

// Db query result: seminar committee members
$rsCommittee = $db->queryCommittee('all');

$prevYear = NULL;
$listHtml = '<ul>';
$tableHtml = '<table>';
while ($row = $rsCommittee->fetch(PDO::FETCH_OBJ)) {
  // Get year from role column
  if (preg_match("/committee-(\d{4})/", $row->role, $matches)) {
    $year = $matches[1];
  } else {
    $year = 'Current';
  }

  // Current committee is listed separately from past members
  if ($year === 'Current') {
    $listHtml .= sprintf('<li><a href="mailto:%s">%s</a>, %s</li>',
      $row->email,
      $row->name,
      $row->phone
    );
  }
  // Past committee members
  else {
    if ($year !== $prevYear) {
      // Add committee members and close tags on previous row
      if (isset($prevYear)) {
        $tableHtml .= implode(', ', $committee);
        $tableHtml .= '</td></tr>';
      }

      // Start a new row
      $committee = [];
      $tableHtml .= "\n<tr><th>$year</th><td>";
    }
    array_push($committee, $row->name);

    $prevYear = $year;
  }
}

// Finish final row
$tableHtml .= implode(', ', $committee);

// Close tags
$listHtml .= '</ul>';
$tableHtml .= '</td></tr></table>';

?>

<p>Please contact the seminar committee to suggest a future topic or speaker:</p>

<?php print $listHtml; ?>

<h2>Past Seminar Committees</h2>

<?php

print $tableHtml;
print $feedHtml;
