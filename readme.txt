=== ONTRApages ===
Contributors: Ontraport, william.deangelis
Tags: landing pages, sales pages, content delivery, coming soon pages, landing page builder
Requires at least: 4.0
Stable tag: 1.2.25
Tested up to: 6.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ONTRApages for WordPress allows Ontraport Premium users to connect to their accounts and easily publish their landing pages on their own WordPress sites.

== Description ==

ONTRApages for WordPress allows Ontraport users to connect to their accounts and easily publish their landing pages on their own WordPress sites.

== Installation ==

1. Upload and unzip the contents of ONTRApages.zip to the `/wp-content/plugins/ontrapages` directory or get it by searching the WP plugin repository.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Do I need an Ontraport account? =

You need an Ontraport account to generate the API credentials for this plugin.

= Where can I learn more about ONTRApages? =

Visit ontraport.com for more information about ONTRApages.

== Screenshots ==

1. /_inc/screenshot-1.png
2. /_inc/screenshot-2.png

== Changelog ==

= 1.2.25 =
* Tested for WordPress version 6.1.1

= 1.2.24 = 
* One more fix for pulling data

= 1.2.23 = 
* Last fix broke pulling for accounts < 50 lp records. Recleaned up code

= 1.2.22 = 
* Fixed issues pulling more than 50 records

= 1.2.21 = 
* Make caching not active when you have extra get params

= 1.2.20 = 
* Fix transient so that get variables dont store different transients

= 1.2.19 = 
* Added new domain criteria when pulling list of available pages

= 1.2.18 =
* Removal of Ontrapages endpoint from plugin

= 1.2.17 =
* Change timeout on landing page list request from 5 -> 60

= 1.2.16 =
* Clean up old files and implement some WP best practices

= 1.2.15 =
* Fixed issue with PHP notice indicating a property of non-object is being accessed.

= 1.2.14 =
* Dropdown choices are now sorted alphabetically.
* Fixed text with old branding and outdated info.

= 1.2.13 =
* Warning cleanup for WP_DEBUG=true

= 1.2.12 =
* Split tests were broken from last update. Fix it again
* Update video & support links in Ontrapages --> settings Admin area

= 1.2.11 =
* Fixed issue with cookies with blank names causing warnings

= 1.2.10 =
* Fixed issue so that the plugin will only pull published ONTRApages

= 1.2.9 =
* Added ability for ONTRAport customers to merge GET variables into their landing pages via merge field syntax
* Fixed issue which caused only the most recent 5 ONTRAPages to show in the static home page drop down menu
* Fixed issue that caused searches containing partial matches to an ONTRAPage's slug to skip the results page, and display that ONTRAPage instead.

= 1.2.8 =
* Fixed issue where all visits would be logged as unique
* Fixed issue that caused split test pages to continuously cycle on refresh
* Fixed issue causing ONTRApages premium customers' hosted pages to not display in some cases
* Fixed issue where having an ONTRApage as a static homepage would show the slug instead of the home url

= 1.2.7 =
* Added support for new ONTRAPages design types
* Added ability to use shortcodes on an ONTRAPage


= 1.2.6 =
* Added more caching around retrieving the HTML content and in case of a failed request to use a stored transient of the last successful call.

= 1.2.5 =
* Added a cache layer that will cache the last successful call to get the HTML of an ONTRAPage in the case we fail to retrieve the HTML in a timely manner the next time around.

= 1.2.4 =
* Fixed issue with Front Page settings not displaying the selected ONTRAPage in the WordPress Settings > Reading options.

= 1.2.3 =
* Updated API call with new condition parameters
* Updated API call with more reliable APPID/APPKEY checks

= 1.2.2 =
* Fixed issue where ONTRAPages would not populate the Front Page settings drop down when using the WordPress Customize Feature
* Removes dependency on CURL.

= 1.2.1 =
* Fixed issue when using built in on error redirects when trying to protect an ONTRAPage using PilotPress

= 1.2 =
* Adds support for ONTRApages.com accounts

= 1.1.8 =
* Fixes issue where there is no warning when you have invalid or missing API credentials in WP 4.4
* Error messages didn't display in the Settings area where you add or update the key pair... only when you go to make an ONTRApage. This is now fixed with BE site wide messages.
* An error would not appear if you added valid API creds and then delete the creds in Ontraport / ONTRApages.
* Upgrades the error handling for invalid API credentials and/or empty ONTRApage objects

= 1.1.7 =
* Fixes issue where an ONTRAPage would not show up when adding it to a menu

= 1.1.6 =
* Fixes issue where plugin does not perform required upgrades on plugin update causing API issues

= 1.1.5 =
* Behind the scenes update to address multiple OP Object types usage in future versions
* Minor bugfix to a patch broken Front page settings from 1.1.4 release

= 1.1.4 =
* Modification to the API calls that removes unnecessary cURL_FOLLOWLOCATION setting that was breaking sites with cURL safe mode enabled
* Modification to the API settings to fix improper naming of the appid and apikey

= 1.1.3 =
* Minor bug fix to address servers that have cURL safe mode enabled

= 1.1.2 =
* Bug fix in the removeSlug function that was causing issues in WP Courseware
* Adds support for adding ONTRApages to the Front (Home) page

= 1.1.1 =
* Labelling Fix for App ID and API Key

= 1.1 =
* Minor text and settings changes

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.2.25 =
* Tested for WordPress version 6.1.1

= 1.2.24 = 
* One more fix for pulling data

= 1.2.23 = 
* Last fix broke pulling for accounts < 50 lp records. Recleaned up code

= 1.2.22 = 
* Fixed issues pulling more than 50 records

= 1.2.21 = 
* Make caching not active when you have extra get params

= 1.2.20 = 
* Fix transient so that get variables dont store different transients

= 1.2.19 = 
* Added new domain criteria when pulling list of available pages

= 1.2.18 =
* Removal of Ontrapages endpoint from plugin

= 1.2.17 =
* Change timeout on landing page list request from 5 -> 60

= 1.2.16 =
* Clean up old files and implement some WP best practices

= 1.2.14 =
* Dropdown choices are now sorted alphabetically.
* Fixed text with old branding and outdated info.

= 1.2.13 =
* Warning cleanup for WP_DEBUG=true

= 1.2.12 =
* Split tests were broken from last update. Fix it again

= 1.2.11 =
* Fixed issue with cookies with blank names causing warnings

= 1.2.10 =
* Fixed issue so that the plugin will only pull published ONTRApages

= 1.2.9 =
* Added ability for ONTRAport customers to merge GET variables into their landing pages via merge field syntax
* Fixed issue which caused only the most recent 5 ONTRAPages to show in the static home page drop down menu
* Fixed issue that caused searches containing partial matches to an ONTRAPage's slug to skip the results page, and display that ONTRAPage instead.

= 1.2.8 =
* Fixed issue where all visits would be logged as unique
* Fixed issue that caused split test pages to continuously cycle on refresh
* Fixed issue causing ONTRApages premium customers' hosted pages to not display in some cases
* Fixed issue where having an ONTRApage as a static homepage would show the slug instead of the home url

= 1.2.7 =
* Added support for new ONTRAPages design types
* Added ability to use shortcodes on an ONTRAPage

= 1.2.6 =
* Added more caching around retrieving the HTML content and in case of a failed request to use a stored transient of the last successful call.

= 1.2.5 =
* Added a cache layer that will cache the last successful call to get the HTML of an ONTRAPage in the case we fail to retrieve the HTML in a timely manner the next time around.

= 1.2.3 =
* Updated API call with new condition parameters
* Updated API call with more reliable APPID/APPKEY checks

= 1.2.2 =
* Fixed issue where ONTRAPages would not populate the Front Page settings drop down when using the WordPress Customize Feature
* Removes dependency on CURL.

= 1.2.1 =
* Fixed issue when using built in on error redirects when trying to protect an ONTRAPage using PilotPress

= 1.2 =
* Adds support for ONTRApages.com accounts

= 1.1.8 =
* Fixes issue where there is no warning when you have invalid or missing API credentials in WP 4.4
* Error messages didn't display in the Settings area where you add or update the key pair... only when you go to make an ONTRApage. This is now fixed with BE site wide messages.
* An error would not appear if you added valid API creds and then delete the creds in Ontraport / ONTRApages.
* Upgrades the error handling for invalid API credentials and/or empty ONTRApage objects

= 1.1.7 =
* Fixes issue where an ONTRAPage would not show up when adding one to a menu

= 1.1.6 =
* Fixes issue where plugin does not perform required upgrades on plugin update causing API issues

= 1.1.5 =
Please upgrade to get the latest version of the ONTRApages WordPress plugin.

= 1.1.4 =
Please upgrade to get the latest version of the ONTRApages WordPress plugin.

= 1.1.3 =
Please upgrade to get the latest version of the ONTRApages WordPress plugin.

= 1.1.2 =
Please upgrade to get the latest version of the ONTRApages WordPress plugin.

= 1.1 =
Please upgrade to get the latest version of the ONTRApages WordPress plugin.

