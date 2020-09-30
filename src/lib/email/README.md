# Seminar emails

Automated seminar announcement emails are created and sent by executing
email.php (via crontab).

The message's responsive HTML template was created using [Foundation for
Emails](https://get.foundation/emails/docs/sass-guide.html) (Sass version).

## Auto-hyphenation

Foundation's settings have auto-hyphenation turned on by default. To turn it
off (recommended), edit normalize.scss in node-modules > foundation-emails >
scss > components, and remove the relevant CSS.

## Source files:

* announcement.html - HTML file for email message with custom HTML tags in Inky
* _announcement.scss - Sass file for styling email message

## Dist file:

* template.html - 'Compiled' responsive HTML template (including inline CSS)
with mustache tags for filling in selected seminar's details
