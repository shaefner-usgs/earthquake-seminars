# Reminder emails

Seminar announcement emails are created and sent automatically by email.php (via crontab).

The message's responsive HTML template was created using [Foundation for Emails](https://foundation.zurb.com/emails/docs/) (Sass version).

Note: Foundation's settings have auto-hyphenation turned on by default. To turn it off, edit normalize.scss is in node-modules > foundation-emails > scss > components, removing the relevant CSS.

## Source files:

announcement.html - HTML file for creating email template (uses custom HTML tags in Inky)
_announcement.scss - Sass file for CSS styles

## Dist file:

template.html - 'Compiled' responsive HTML template for Seminar email message
