=== Import User Meta Data from CSV ===
Contributors: qlstudio
Tags: user, users, import, usermeta,  user_meta, bulk, manage, CSV
Requires at least: 3.5
Tested up to: 4.4.1
Stable tag: 0.3.2
License: GPLv2

Bulk import user meta data from a text list ( csv ) - checking for missing users & existing usermeta data to avoid duplicates.

== Description ==

WordPress admins can bulk import user metadata from a text file - selecting the Key and optionally avoiding duplicate keys.

This plugin uses up-to-date WordPress top level functions, sanitizes all input data and is fully internationalized.

For feature request and bug reports, [please use the WP Support Website](http://www.wp-support.co/view/categories/import-user-meta-data-from-csv).

Please do not use the Wordpress.org forum to report bugs, as we no longer monitor or respond to questions there.

= Features =

* Bulk import users from a simple text list
* Select if duplicate meta_keys should be added
* Checks for missing users
* Sample import data and easy to use
* Fully internationalized
* Safe and WordPress Friendly
* Nerdy debug screens!

For feature request and bug reports, [please use the forums](http://wordpress.org/tags/add-user-metadata).

== Installation ==

For an automatic installation through WordPress:

1. Go to the 'Add New' plugins screen in your WordPress admin area
1. Search for 'Add User Metadata'
1. Click 'Install Now' and activate the plugin
1. Go the 'Users' menu, under 'Add User Metadata'

For a manual installation via FTP:

1. Upload the `export-user-data` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' screen in your WordPress admin area
1. Go the 'Users' menu, under 'Add User Metadata'

To upload the plugin through WordPress, instead of FTP:

1. Upload the downloaded zip file on the 'Add New' plugins screen (see the 'Upload' tab) in your WordPress admin area and activate.
1. Go the 'Users' menu, under 'Add User Metadata'

== Frequently Asked Questions ==

= How to use? =

Click on the 'Add User Metadata' link in the 'Users' menu, enter the meta_key and paste in your CSV data, select the import options then click 'Add Metadata'. That's all!

== Screenshots ==

1. Add Metadata screen

== Changelog ==

= 0.3.2 =
* WP 4.4.1 testing

= 0.3.1 =
* WP 4.0 testing

= 0.3 =
* moved plugin instatiation to WP init hook
* Name change
* 3.8.1 testing

= 0.2 =
* Contributor Edit.

= 0.1 =
* First public release.

== Upgrade Notice ==

= 0.1 =
First release.
