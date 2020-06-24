<?php

include_once '../conf/config.inc.php'; // app config

if (!isset($TEMPLATE)) {
  $TITLE = 'Seminar Email List';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="'. $MOUNT_PATH . '/css/email-list.css" />';
  $FOOT = '';

  include 'template.inc.php';
}

?>

<p>Subscribe (or unsubscribe) to our seminar announcement email list.</p>

<p>Two email reminders are sent for each seminar: one 2 days before and one
  2.5 hours before the seminar begins.</p>

<?php

if (isSet($_POST['submitbutton'])) {

$action = 'Add User';
if ($_POST['option'] === 'unsubscribe') {
  $action = 'Remove User';
}
$headers = [
  'Content-Type: text/html; charset=ISO-8859-1',
  'From: webmaster@' . $_SERVER['SERVER_NAME'],
  'MIME-Version: 1.0'
];
$message = sprintf('
  <dl>
    <dt>Name</dt>
    <dd>%s</dd>
    <dt>Email</dt>
    <dd>%s</dd>
  </dl>',
  $_POST['name'],
  $_POST['email']
);
$subject = 'Seminar Email List: ' . $action;
$to = $LIST_EMAIL;

mail($to, $subject, $message, implode("\r\n", $headers));

?>

<p class="success">Your request was received. Please allow a few days for
  processing.</p>

<?php

} else {

?>

<section class="form">
  <form action="./email-list.php" method="POST">
    <div class="group">
      <div class="control radio pretty p-default p-pulse p-round">
        <input id="subscribe" name="option" type="radio" value="subscribe" tabindex="1" checked="checked">
        <div class="state p-primary-o">
          <label for="subscribe">Subscribe</label>
        </div>
      </div>
      <div class="control radio pretty p-default p-pulse p-round">
        <input id="unsubscribe" name="option" type="radio" value="unsubscribe" tabindex="2">
        <div class="state p-primary-o">
          <label for="unsubscribe">Unsubscribe</label>
        </div>
      </div>
    </div>
    <div class="control text">
      <input id="name" name="name" type="text" value="" required="required" tabindex="3">
      <label for="name">Name</label>
    </div>
    <div class="control email">
      <input id="email" name="email" type="email" value="" pattern="[^@]+@[^@]+\.[^@]+" required="required" tabindex="4">
      <label for="email">Email Address</label>
    </div>
    <input id="submitbutton" name="submitbutton" type="submit" class="btn btn-primary" tabindex="5" value="Submit">
  </form>
  <p class="required"><span>*</span> = required field</p>
</section>

<?php } ?>
