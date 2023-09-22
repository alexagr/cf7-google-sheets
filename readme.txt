=== Google Sheets for Contact Form 7 ===
Contributors: alexagr
Donate link: https://paypal.me/alexagr
Tags: Contact Form 7, Google Sheets, Google, Sheets
Requires at least: 3.6
Tested up to: 6.3.1
Stable tag: 1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Send your Contact Forms 7 submissions directly to your Google Sheets spreadsheet.

== Description ==

This plugin provides integration between [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) and [Google Sheets](https://www.google.com/sheets/).
It adds new processor to Contact Form 7 that enables sending of submitted forms to Google Sheets.  

= Connecting the Plugin to Google Sheets =

After installing the plugin you must go to it's **Settings** screen and create application credentials needed to establish connection with Google Sheets. All instructions are provided in the **Settings** screen. 

After successfully creating application credentials and uploading them (i.e. completing steps 1 and 2), you will see *Client ID* and *Client Email* that represent your instance of "Google Sheets for Contact Form 7" plugin. You will need to share your sheets with *Client Email* (as *Editor* role) to grant "Google Sheets for Contact Form 7" plugin permissions to edit them.

= Using the Plugin =

After successfully connecting the plugin to Google Sheets, do the following to configure your Contact Form 7 to send data to Google Sheets on form submission.

*In Google Sheets*  

* Create a new Google Sheet
* Switch to the tab where you want to capture the data
* Determine *Sheet ID* and *Tab ID* from the sheet's URL that looks as follows: https://docs.google.com/spreadsheets/d/<sheet-id>/edit#gid=<tab-id>

  For example, for URL https://docs.google.com/spreadsheets/d/142XLjqRCpr7iWldfARhSS_GiFoy4l5RqZfSD6RHXM64/edit#gid=0 *Sheet ID* is "142XLjqRCpr7iWldfARhSS_GiFoy4l5RqZfSD6RHXM64" and *Tab ID* is "0"

* Enter "datetime" in the the first column if you want to capture time and date of submission
* There is no need to enter names for other columns - they will be automatically added upon form submission
* Share the sheet with *Client Email* that represents your instance of "Google Sheets for Contact Form 7" plugin - as *Editor* role

*In Contact Form 7*  

* Open your contact form
* Switch to **Google Sheets** tab
* Configure *Sheet ID* and *Tab ID* and click *Save*
* Submit a test form and verify that the data shows up in your Google Sheet

= Automatic Header Generation = 

The plugin verifies spreadsheet header on each new form submission and adds new fields to it if needed. Note that it never deletes fields from the header - as this would also delete some submission data - though you can do it manually. You may also manually reorder columns as you wish.

= Integration with "Contact Form 7 Database Addon - CFDB7" = 

If you use [Contact Form 7 Database Addon - CFDB7](https://wordpress.org/plugins/contact-form-cfdb7/) to save your submissions, you will also be able to resend already submitted forms to Google Sheets. This may be useful if something went wrong during initial form submission and/or someone deleted data in Google Sheets by mistake. In order to do so, in CFDB7 plugin, choose your form, open specific submission and click *Send to Google Sheets* button.

= Acknowledgements =

This plugin is inspired by [CF7 Google Sheets Connector](https://wordpress.org/plugins/cf7-google-sheets-connector/) plugin.

However it's implementation is quite different:

* it uses service principle for authentication with Google Sheets
* it implements automatic header generation
* it has more straightforward (and hopefully more reliable) sheets update logic

All this results in a more reliable integration.

== Screenshots ==

1. Connecting plugin to Google Sheets
2. Configuring Google Sheets processor in Contact Form 7
3. Integration with "Contact Form 7 Database Addon - CFDB7" plugin

== Installation ==

1. Upload "cf7-google-sheets" to the "/wp-content/plugins/" directory
2. Activate the plugin through the **Plugins** screen in WordPress  

== Frequently Asked Questions ==

= Why isn't the data sent to spreadsheet? Contact Form 7 Submit is just Spinning. =

Sometimes it can take a while of spinning before it goes through. But if the entries never show up in your Google Sheet use the following checklist:

* Check that plugin can access your sheet by entering it's *Sheet ID* in **Settings** screen and clicking *Test*
* In Google Sheets processor configuration screen for your form:
  * Check that you entered correct *Sheet ID* and *Tab ID* (obtained from the sheet's URL - and NOT the Sheet/Tab Name)
* Check *View Log* in plugin **Settings** screen for detailed error trace 

== Changelog ==

= 1.1 =
* Add integration with "Contact Form 7 Database Addon - CFDB7"

= 1.0 =
* Initial version

