=== Campaign Monitor Dual Registration ===
Contributors: carloroosen, pilotessa
Donate link:
Tags: Campaign Monitor, user management, mailing list
Requires at least: 3.0.1
Tested up to: 3.8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Subscribe new Wordpress user to your mailing list on Campaign Monitor. 

== Description ==

This plugin automatically adds new Wordpress users to Campaign Monitor. Modifications on the Campaign Monitor list can be copied back to the Wordpress list, this is not yet documented. 

= Relation to our Campaign Monitor Synchronization plugin =

This plugin is written as an alternative for our plugin "Campaign Monitor Synchronization". It keeps both lists in full sync, where the WordPress user list is the master. For a lot of use cases this behavior is too rigorous, therefore we created this new plugin.

= Links =

* [Author's website](http://carloroosen.com/)
* [Plugin page](http://carloroosen.com/campaign-monitor-synchronisation/)

== Installation ==

1. Register on http://campaignmonitor.com and create a list. Don't use an existing list, the data will be lost !
1. In the list details click the link "change name/type", there you will find the list ID, it is a 32 character hexadecimal string. Don't use the list ID in the url!.
1. Go to your account settings. There you will find the API key, it is also a 32 character hexadecimal string.
1. On your wordpress website, upload `campaign-monitor-dual-registration.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. In the plugin options, enter the list ID and API key.
1. Select which fields you want to copy to Campaign Monitor. E-mail address will always be copied.


== Changelog ==

= 1.0.1 =
* First commit
