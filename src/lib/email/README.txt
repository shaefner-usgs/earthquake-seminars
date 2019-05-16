Reminder emails are created and sent automatically by email.php (via crontab).

The message's responsive HTML template was created using Foundation for Emails (Sass version) <https://foundation.zurb.com/emails/docs/>

(Required editing normalize.scss is in node-modules > foundation-emails > scss > components to remove auto-hyphenation)

Source files:

seminars.html - HTML file for creating email template (uses custom HTML tags in Inky)
_seminars.scss - Sass file for CSS styles

Dist file:

template.html - 'Compiled' responsive HTML template for Seminar email message
