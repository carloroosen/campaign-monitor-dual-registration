=== Campaign Monitor Dual Registration ===
Contributors: carloroosen, pilotessa
Tags: Campaign Monitor, user management, mailing list, add users
Requires at least: 3.0.1
Tested up to: 3.9
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically add new Wordpress users to your mailing list on Campaign Monitor. 

== Description ==

This plugin automatically adds new Wordpress users to your Campaign Monitor list. 

* The moment a user is added to WordPress, its email address (and optionally) user-meta data will be copied to Campaign Monitor.
* Also, when user data is changed, the modifications will be sent to Campaign Monitor
* Changes on Campaign Monitor can be reflected back to the user list, to use this feature one needs to define webhooks on the Campaign Monitor website. 

= Relation to our Campaign Monitor Synchronization plugin =

Our plugin [Campaign Monitor Synchronization](http://wordpress.org/plugins/campaign-monitor-synchronization) also adds WordPress users to Campaign Monitor, but it uses a more strict synchronization mechanism. For instance, it also removes users from the Campaign Monitor list when they do not exist as Wordpress users. For a lot of use cases this behavior is too rigorous, therefore we created this new plugin.

= Links =

* [Author's website](http://carloroosen.com/)
* [Plugin page](http://carloroosen.com/campaign-monitor-dual-registration)

== Installation ==

1. Register on http://campaignmonitor.com and create a list, if you haven't done this already.
1. In the list details click the link "change name/type", there you will find the list ID, it is a 32 character hexadecimal string. Don't use the list ID in the url!.
1. Go to your account settings. There you will find the API key, it is also a 32 character hexadecimal string.
1. On your wordpress website, upload `campaign-monitor-dual-registration.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. In the plugin options, enter the list ID and API key.
1. Select which fields you want to copy to Campaign Monitor. E-mail address will always be copied.


== Changelog ==

= 1.0.1 =
* First commit
= 1.0.2 =
* support for 1000+ users
= 1.0.3 =
* Fix some notices.
= 1.0.4 =
* Fix subscribers import bug.
