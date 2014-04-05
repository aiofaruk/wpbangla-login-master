=== WPBangla Login Master ===
Author: Shameem Reza, Omar Faruk, Al-amien Gazi
Author URI: http://shameemreza.info
Donate link: http://offlink.us/36Z
Tags: login, wpbangla, attack, authentication, ban ip, captcha, exploit, hack, hackers, password, permissions, protect, script kiddie, security, security scan, website defender
Requires at least: 3.5.0
Tested up to: 3.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

All-in-one solution for all login related security problems.

== Description ==

'WPB Login Master' Your site bodyguard need it. It will take care of your site login related security.

= Feature =

* Automatically ban IPs that brute-force attack you.
* Detailed log of all login-related activities.
* Redirect users based on roles and usernames.
* Get email notifications for all login events.
* Protect site from brute-force login attacks.
* Protect login & register forms with captcha.
* Stop bots from registering.
* Manually ban any IP.

= A Security Plugin From <a href="http://www.facebook.com/groups/Wordpress2Smashing/">WordPress Group Bangladesh</a>. =

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

== Screenshots ==

1. Redirectins
2. Setting
3. Banned IPs
4. Log
5. After Logout
6. Before Login
7. Wrong Password Error
