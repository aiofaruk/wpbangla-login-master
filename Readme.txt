=== WPBangla Login Master ===
Contributors: TheAnuvhuti
Donate link: http://offlink.us/36Z
Tags: login, wpbangla, attack, authentication, ban ip, captcha, exploit, hack, hackers, password, permissions, protect, script kiddie, security, security scan, website defender
Requires at least: 3.5.0
Tested up to: 3.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

All-in-one solution for all login related security problems.

== Description ==

'WPB Login Master' Your site bodygurd need it. It will takecare of your site login related security.

= Feature =

* Automatically ban IPs that brute-force attack you.
* Detailed log of all login-related activities.
* Redirect users based on roles and usernames.
* Get email notifications for all login events.
* Protect site from brute-force login attacks.
* Protect login & register forms with captcha.
* Stop bots from registering.
* Manually ban any IP.
* Native, easy to use WP GUI.

** Online Doccumentation: http://demo.shameemreza.info/wpblogin

= A Security Plugin From WordPress Group Bangladesh: http://www.facebook.com/groups/Wordpress2Smashing/

== Installation ==

1. Download the ZIP package.
2. Open WordPress admin and go to Plugins -> Add New -> Upload. Browse for the ZIP file wpb-login-master.zip on your computer and hit “Install Now”.
3. Activate the plugin.

= Requirements =

* WordPress v3.5 or greater

== Frequently Asked Questions ==

= Will it work on my theme? =

Yes! WPB Login Master works with all themes.

= Will it work with my plugins? =

It will work with all non-security related plugins. Please contact us if you want to use it with other security plugins.

= Will it work with my custom login form? =

Yes if your form uses all actions and filters needed by WPB Login Master. Otherwise it wont.

= Will this plugin slow my site down? =

No.

= I banned myself! What to do? Help! =

There are several options to remove the ban:

* Wait until the ban is lifted
* Login on the site via another IP and delete the ban
* Open your WP database, wp_options table and delete the row that has “option name” set to “wpb_login_master_banned”
* Open wpb-login-master.php and place the following code as the first line of init() function (around line #29): clear_banned();

== Configuration ==

All WPB Login Master’s options are configured in WP admin under Settings – WPB Login Master.

= Redirections by user roles =

After login users that have a certain role can be redirected using one of five available rules. Custom user roles are fully supported.

* Default (normal) behaviour – same behaviour as if you don’t have WPB Login Master installed
* Disable login – prevents users from logging in. This option is not available for admin role.
* Redirect to a custom URL – redirect users to any specified URL. Variables can be used to make the URL dynamic; {username} – current user’s username, {user_id} – current user’s ID, {month} – current month with leading zeros, {day} – current day with leading zeros, {year} – current year (4 digits), {site_url} – full site URL.
* Redirect to a random post – you can choose a category or set it to “all categories”
* Redirect to the most recent post – you can choose a category or set it to “all categories”

= Redirections by users =

After login users can be redirected using one of five available rules. Please note that redirections defined on usernames are parsed before the ones on roles. Rules are the same as for roles, see above.

= Banned IPs =

IP can get banned in two ways; automatically by failing to login x times in y minutes (see options tab), or manually by entering the IP. IPs are entered in standard notation: abc.abc.abc.abc, wildcards (*, %) are not supported and leading zeroes have to be removed. Ie this is not good: 192.168.001.017, it should read 192.168.1.17. Bans can be manually deleted or they will be automatically removed once the ban time passes.

For automatically banned IPs ban time is set in options, while for manual ones you can set the time to your liking in the “Add new ban” dialog. Depending on the set option banned IPs will either be blocked from the entire site and a custom message (see options) will be displayed to them, or they’ll be able to access the site but wont be able to login until ban is removed.

= Log =

Log displays all events on the site related to logins, logouts, failed logins and bans. You can search the records and sort them in ascending or descending order.

“Delete all log entries” will completely remove all log entries. Please note that **there’s no undo.**

= Settings – Ban rules =

* Maximum number of failed login attempts before ban – defines the number of failed attempts user has to make in a set amount of time before his IP gets banned. Please note that opening the login form or just clicking the “Login” button does not count as a failed attempt. You have to enter a username and a password and click “Login”.
* Default ban time – when an IP gets auto-banned this is the amount of the user wont be able to login/access the site. After the time passes ban is automatically removed.
* Banned users – are not able to login (but can view the site) or they can’t access the whole site and the only thing they see is the ban message.
* Message for banned users – if option above is set to “can’t access the whole site” this is the message banned users will see. HTML is fully supported.
* Login notice – message displayed above login form to inform users they might get banned. HTML is fully supported.

= Settings – Email notifications =

* Notification e-mail – admin’s e-mail where the notifications will be send to.
* Send notification on failed login – admin will get an email for every failed login.
* Send notification on successful login – admin will get an email for every successful login.
* Send notification when new IP gets banned – admin will get an email every time someone gets banned. We recommend keeping this setting enabled.

= Settings – Captcha settings =

* Protect login and register forms with captcha – if enabled those two forms will be protected by an easy to read and solve captcha image.
* Captcha background color – captcha image background color. Transparent is supported.
* Captcha question text – text displayed before captcha image on login and register forms.
* Add noise to captcha image so it is more difficult to read – adds noise to the image making it more difficult to read for humans and bots.

= Settings – Other settings =

Redirect URL on logout – on logout users will be redirected to the defined URL. You can make the URL dynamic by using the following variables: {month}, {day}, {year}, {site_url}.