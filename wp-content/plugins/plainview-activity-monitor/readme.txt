=== Plainview Activity Monitor ===
Contributors: edward_plainview
License: GPLv3
Requires at least: 3.9
Stable tag: 20180826
Tags: activities, activity, logging
Tested up to: 4.9.8

Real-time monitoring of users, content, functionality, appearance, security, and updates.

== Description ==

Activity Monitor tracks all user activity on your blog or network. The activities can be viewed in global table showing activities on the whole network, or locally for just the blog you are currently viewing. The activites can be filtered so that only specific blogs / hooks / IPs / users are displayed.

Monitored actions include (not exhaustive):

* Comments: approve, held, spam, delete
* Custom Post Types:  draft, publish, update, trash, delete
* Logins: login, login failed, logout
* Pageviews: admin, front-end
* Passwords: reset, retrieve
* Plugins: activate, deactivate
* Pages: draft, publish, update, trash, delete
* Posts: draft, publish, update, trash, delete, password
* Taxonomies: create, edit, delete
* Themes: switched
* Updates: WordPress core, plugins, themes
* Users: register, delete, profile changes
* And more....

The logged information consists of:

* A description of what was logged
* Blog
* Timestamp
* Hook that was triggered
* User ID

= git =

The <a href="https://bitbucket.org/edward_electric/activity-monitor">Activity Monitor has a git repository</a>.

= Security tips =

There are several ways for people to break in to your Wordpress installation, or cause trouble by DDOS. Here are some tips on how to use the Activity Monitor and its plugins to help detect problems:

* Get a DDOS protection service with an API. There is a plugin to ban IPs via CloudFlare (<em>Send To CloudFlare</em>). Other APIs could be supported as the need arises.
* If you have another DDOS service, write a script that can ban visitors by IP. Use this script with the <em>Send To Exec</em> plugin.
* If you can't ban users using a script, at least set up the <em>Send To E-mail</em> plugin to inform you of suspicious activity.
* Use the Bruteforce Detect plugin to detect when an IP or IPs are trying to guess the admin's password. Ban the IPs automatically using <em>Send To Exec</em>.
* Do not use admin as the username for your administrator account. Instead, use some else and add the admin username to the list of banned usernames in the <em>Login Failed Username</em> plugin. Ban the IPs that cause the plugin to react.

= Custom hooks =

See the <a href="https://pvactivitymonitor.com/doc/dev/">developer documentation</a> for relevant info on how to create custom hooks.

== Installation ==

1. Check that your web host has PHP v5.4.
1. Activate the plugin, preferrably network wide.
1. Go to Activity Monitor > Logged Hooks, and go through all of the hooks that are interesting.

== Screenshots ==

1. Global activity overview
2. Local activity overview
3. Filter tab
4. Logged hooks tab
5. Mass delete tool
6. Settings - Activities
7. Settings - Debug
8. Settings - Display
9. Tools tab
10. Uninstall tab that removes the plugin from the database

== Frequently Asked Questions ==

= I need support! =

Use the Wordpress support forum.

= I do not have PHP 5.4. Can I run the Activity Monitor? =

No. PHP 5.4 not even supported officially anymore. It is time for your web host to upgrade.

== Changelog ==

= 20180826 =

* Fix IP filter on tools tab
* SDK update

= 20161228 =

SDK update. Includes fixed PHPmailer class, which is never used by the Activity Monitor, but could trigger warnings from webhosts that do file version checks.

= 20160210 =

New <a href="https://pvactivitymonitor.com/">Activity Monitor Premium Pack</a> released.

= 20160202 =

* New: Detects real user IP using HTTP_X_FORWARDED_FOR in case the server is behind a load balancer.
* Fix: Local activity tab works again.
* Fix: Removed warning when uploading a new plugin. We don't know anything about the plugin so we cannot save any plugin version info.
* Code: Cache the results of manifest_hooks to prevent it from returning new objects.

= 20160107 =

* New: Update hooks for core, plugins and themes.
* Obsolete: wp_login_failed_without_password hook. Use new display setting to hide failed password.

= 20151224 =

* New: Added freetext search filter.
* New: Display ALL available hooks in the hooks filter, not only those that exist in the database, although those are also included.
* Fix: All hooks are logged on new installs.
* Code: list_distinct_values puts the values as keys, instead of values, in the collections.

= 20151211 =

* New: Mass delete tool.
* Code: log_hook, prune_activites and remove_activities now have priority 100, to allow other plugins to get to the action first.

= 20151206 =

* New: Specialized comment hooks have been added.
* New: post_password hook shows the entered password for protected posts.

= 20151119 =

* Fix: Only remove non-existent logged hooks from the table when modifying the list of logged hooks, instead of upon viewing the list.
* Clear filter button also closes filter popup.
* Filter buttons are bolded when filters are active.

= 20151112 =

* New: Filter a maximum amount of activities.
* New: Filter the activities by age.
* New: Tab the filter settings screen for a better overview.
* Fix: AM works again in single blog mode.

Developer info:

* The data column in the activities are now stored as just serialized arrays, and not serialized+base64 encoded strings. Conversion is done transparently upon load. See src/database/activity.php
* filter settings actions: add_filter_settings and save_filter_settings, if you want to build your own filters.
* list_activities has gotten a lot of work. It now uses the filter settings from the filters_settings property, querying it for the correct filter settings.

= 20151106 =
* New: delete_term hook when a term is deleted.
* New: edit_term and edited_term hooks. Only the latter generates a real activity.
* New: switch_theme hook.
* New: Activity Monitor now available in the network admin menu.
* Fix: Disable sorting of table when clicking the checkbox.
* Fix: Disable hook logging into the db if requested to do so by, for example, the bbpress AM plugin.
* Info about plugin pack removed until the new, enhanced pack is ready for release.

= 20151021 =
* New: Visible rows in the activity list can be sorted by clicking the column headers.
* Remove info about plugin pack. Site is being redone in preparation for launch of new, more fully-featured pack.
* Fix: Allow the table to be sorted even after an autorefresh.
* Code: html_and_execute() from log_hook now allows inline text after html() object.
* Code: New actions: get_activity_table_columns and display_activity_table_column allow for custom table columns.
* SDK update: better debugging options.

= 20150806 =
* Activities table now autorefreshes. There is an autorefresh setting.
* Filters can be modified via buttons on the same page as the activities table.
* Hook grouping.
* Code: git moved to https://bitbucket.org/edward_electric/activity-monitor

= 20150508 =
* Fixed mysqli error.

= 20141211 =
* New: Send To Log plugin added to plugin pack.
* New: HTTP POST plugin added to plugin pack.
* Code: SDK update.

= 20141123 =
* New hooks: activated_plugin and deactivated_plugin.
* Code: Enable same action classes to report different hooks.

= 20141023 =
* New action: wp_head is triggered when a visitor views the front-end. Note that the action is not triggered if you have caching enabled.
* New action: admin_head for views to the admin panel.
* Code: Hide sprintf error in hook data.

= 20141016 =
* Fix: Activity timestamps use local timezone when being displayed.

= 20141013 =
* Fix: If on a network, only super admins may see the menu.

= 20141006 =
* Fix: sprintf error is hidden to prevent problems.
* New: Plugin pack released.

= 20140814 =
* Fix: Prune activities bug fixed.

= 20140708 =
* New: Prune setting keeps a maximum amount of activities in the database. Standard is one million rows.
* Fix: IP filtering in the overview works now.
* Code: Hook->get_vendor, get_description and get_hook are not static anymore.
* Code: New action: get_logged_hooks allows plugins to add hooks dynamically.
* Code: Hooks have their name stored in ->hook.

= 20140623 =
* Fix: Posts without titles get their ID as the title.

= 20140615 =
* New: Better compatability with Windows servers.
* Code: SDK update.

= 20140605 =

* New: Table has CSS classes.
* Fix: Fix for single blog installs.

= 20140521 =

* Fix: Better support for single blog installs (switch_to_blog() checks).

= 20140520 =

* Code: Hooks are loaded after Wordpress has finished loading.

= 20140511 =

* Initial version.
