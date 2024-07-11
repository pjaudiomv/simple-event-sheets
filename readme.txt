=== Simple Event Listing feed from Google Sheets ===

Contributors: pjaudiomv
Tags: event listing, events, google sheets
Requires PHP: 8.0
Tested up to: 6.5.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

**Simple Event Listing feed from Google Sheets** is a plugin designed to fetch event data from a Google Spreadsheet and display it on your website.

== Description ==

**Simple Event Listing feed from Google Sheets** is a plugin designed to fetch event data from a Google Spreadsheet and display it on your website.

SHORTCODE
- Basic Usage: `[simple_event_sheets]`
    * Ensure your Google Sheet has the row headers: `date, name, url, event_info, truck_info`. The date should be formatted as mm/dd/yyyy. Implement data validation on the date and url rows to prevent errors. Note: This plugin also offers built-in data validation.
    * Regarding the Google API Key: You'll need an API key with Spreadsheet access. The sheet should either be set to "anyone with the link can view" or you should add a service user. If you're utilizing server-side event loading, restrict the key by server IP. For client-side loading, restrict the key by domain.

You can use this Google Sheet as a template if wanted, it includes data and url validation to help ensure good data quality. https://docs.google.com/spreadsheets/d/18NnmKKU7P6bFOPEHgyUMWeKQWQJnAfYb5gmn0-fne1E/

### Third-Party Service Disclosure

This plugin relies on a third-party service, Google Sheets, to function properly. The plugin fetches data from Google Sheets under the following circumstances:

- When retrieving event data to display within the application.

## Service Information

- **Service:** [Google Sheets API](https://developers.google.com/sheets/api)
- **Terms of Use:** [Google API Terms of Use](https://developers.google.com/terms/)
- **Privacy Policy:** [Google Privacy Policy](https://policies.google.com/privacy)

MORE INFORMATION

<a href="https://github.com/pjaudiomv/simple-event-sheets" target="_blank">https://github.com/pjaudiomv/simple-event-sheets</a>

== Installation ==

This section describes how to install the plugin and get it working.

1. Download and install the plugin via the WordPress dashboard, or upload the entire **Simple Event Listing feed from Google Sheets** folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Insert the `[simple_event_sheets]` shortcode into your WordPress page or post.

== Changelog ==

= 1.0.0 =

* Initial Release
