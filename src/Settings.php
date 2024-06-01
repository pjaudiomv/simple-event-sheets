<?php

namespace SimpleEventSheets;

class Settings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_menu', [$this, 'createMenu']);
    }

    public function registerSettings(): void
    {
        register_setting('simple-event-sheets-group', 'simple_event_sheets_sheet_id', 'sanitize_text_field');
        register_setting('simple-event-sheets-group', 'simple_event_sheets_sheet_name', 'sanitize_text_field');
        register_setting('simple-event-sheets-group', 'simple_event_sheets_google_api_key', 'sanitize_text_field');
        register_setting('simple-event-sheets-group', 'simple_event_sheets_checkbox');
        register_setting('simple-event-sheets-group', 'simple_event_sheets_passed_events_checkbox');
    }

    public function createMenu(string $baseFile): void
    {
        add_options_page(
            esc_html__('Simple Event Sheets Settings'), // Page Title
            esc_html__('Simple Event Sheets'),          // Menu Title
            'manage_options',                  // Capability
            'simple-event-sheets',                      // Menu Slug
            [$this, 'drawSettings']            // Callback function to display the page content
        );
        add_filter('plugin_action_links_' . $baseFile, [$this, 'settingsLink']);
    }

    public function settingsLink($links)
    {
        $settings_url = admin_url('options-general.php?page=simple-event-sheets');
        $links[] = "<a href='{$settings_url}'>Settings</a>";
        return $links;
    }

    public function drawSettings(): void
    {
        ?>
        <div class="wrap">
            <h2>Simple Event Sheets Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('simple-event-sheets-group'); ?>
                <?php do_settings_sections('simple-event-sheets-group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Google Sheet ID</th>
                        <td>
                            <input type="text" size="45" name="simple_event_sheets_sheet_id" value="<?php echo esc_attr(get_option('simple_event_sheets_sheet_id')); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Google Sheet Name</th>
                        <td>
                            <input type="text" size="45" name="simple_event_sheets_sheet_name" value="<?php echo esc_attr(get_option('simple_event_sheets_sheet_name')); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Google API KEY</th>
                        <td>
                            <p>Make sure to set referrer restrictions. Domain for client side or IP for server side loading .</p>
                            <input type="text" size="45" name="simple_event_sheets_google_api_key" value="<?php echo esc_attr(get_option('simple_event_sheets_google_api_key')); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Load Sheet Server Side</th>
                        <td>
                            <input type="checkbox" name="simple_event_sheets_checkbox" value="1" <?php checked(1, get_option('simple_event_sheets_checkbox'), true); ?> />
                            <label for="simple_event_sheets_checkbox">This will load the data server side and pass to the client. (Defaults to Client Side for faster loading)</label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show past events</th>
                        <td>
                            <input type="checkbox" name="simple_event_sheets_passed_events_checkbox" value="1" <?php checked(1, get_option('simple_event_sheets_passed_events_checkbox'), true); ?> />
                            <label for="simple_event_sheets_passed_events_checkbox">This will show past events. (Defaults to not displaying them.)</label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
