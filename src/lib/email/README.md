# Seminar emails

Automated seminar announcement emails are created and sent by executing
email.php (via esc user's crontab).

The messages' responsive HTML templates were created using [Foundation for
Emails](https://get.foundation/emails/docs/sass-guide.html) (Sass version).

## Auto-hyphenation

Foundation's settings have auto-hyphenation turned on by default. To turn it
off (recommended), edit _normalize.scss in node-modules > foundation-emails >
scss > components, and remove the relevant CSS.

## Source files

HTML files with custom Inky tags; Sass file for inlined CSS

* template-src.html
* template-no-seminar-src.html
* template-src.scss

## Dist files

'Compiled' responsive HTML templates (including inline CSS) with mustache tags 
for filling in selected seminar's details

* template.html
* template-no-seminar.html
