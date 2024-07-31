=== Simple Event Listing feed from Google Sheets ===

Contributors: pjaudiomv
Tags: event listing, events, google sheets
Requires PHP: 8.0
Tested up to: 6.6.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

**Simple Event Listing feed from Google Sheets** is a plugin designed to fetch event data from a Google Spreadsheet and display it on your website.

== Description ==

**Simple Event Listing feed from Google Sheets** is a plugin designed to fetch event data from a Google Spreadsheet and display it on your website.

SHORTCODE
- Basic Usage: `[simple_event_sheets]`
    * Ensure your Google Sheet has the row headers: `date, name, url, event_info, day_info`. The date should be formatted as mm/dd/yyyy. Implement data validation on the date and url rows to prevent errors. Note: This plugin also offers built-in data validation.
    * `event_info`: Additional information about the event, displayed next to the event name.
    * `day_info`: Additional information about the day of the event, displayed next to the date.
    * Regarding the Google API Key: You'll need an API key with Spreadsheet access. The sheet should either be set to "anyone with the link can view" or you should add a service user. If you're utilizing server-side event loading, restrict the key by server IP. For client-side loading, restrict the key by domain.

You can use this Google Sheet as a template if wanted, it includes data and url validation to help ensure good data quality. https://docs.google.com/spreadsheets/d/18NnmKKU7P6bFOPEHgyUMWeKQWQJnAfYb5gmn0-fne1E/

### Third-Party Service Disclosure

This plugin relies on a third-party service, Google Sheets, to function properly. The plugin fetches data from Google Sheets under the following circumstances:

- When retrieving event data to display within the application.

## Service Information

- **Service:** [Google Sheets API](https://developers.google.com/sheets/api)
- **Terms of Use:** [Google API Terms of Use](https://developers.google.com/terms/)
- **Privacy Policy:** [Google Privacy Policy](https://policies.google.com/privacy)

### Creating a Google API Key with Sheets API Access

1. **Go to the Google Cloud Console:**
   - Open the Google Cloud Console at [console.cloud.google.com](https://console.cloud.google.com/).

2. **Create a New Project:**
   - Click on the project dropdown and select "New Project".
   - Enter a project name and click "Create".

3. **Enable the Sheets API:**
   - With your project selected, go to the [API Library](https://console.cloud.google.com/apis/library).
   - Search for "Google Sheets API" and click on it.
   - Click "Enable" to enable the API for your project.

4. **Create API Credentials:**
   - Go to the [Credentials](https://console.cloud.google.com/apis/credentials) page.
   - Click "Create Credentials" and select "API key".
   - Your API key will be created. Copy it and keep it safe.

5. **Restrict Your API Key:**
   - Click on the edit icon next to your API key.
   - Under "Key restrictions", select either "HTTP referrers (web sites)" or IP (server).
   - Add the referrer(s) for your site, such as `https://yourdomain.com/*` or Server IP.
   - Save your changes.

 6. **Set Spreadsheet Access:**
    - Ensure your Google Sheet is either set to "anyone with the link can view" or you should add a service user with the necessary permissions. This step is crucial for the API key to access the data.

### MORE INFORMATION

<a href="https://github.com/pjaudiomv/simple-event-sheets" target="_blank">https://github.com/pjaudiomv/simple-event-sheets</a>

== Installation ==

This section describes how to install the plugin and get it working.

1. Download and install the plugin via the WordPress dashboard, or upload the entire **Simple Event Listing feed from Google Sheets** folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Insert the `[simple_event_sheets]` shortcode into your WordPress page or post.

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png

== Changelog ==

= 1.0.1 =

* Updated event and day info.

= 1.0.0 =

* Initial Release
