<?php

namespace SimpleEventSheets;

class Settings {

	private const PLUG_SLUG = 'simple-event-listing-feed-from-google-sheets';

	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'create_menu' ] );
	}

	public function register_settings(): void {
		// Existing settings
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_sheet_id', 'sanitize_text_field' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_sheet_name', 'sanitize_text_field' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_google_api_key', 'sanitize_text_field' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_checkbox' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_passed_events_checkbox' );

		// New calendar-specific settings
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_default_view', 'sanitize_text_field' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_show_view_toggle' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_calendar_start_day', 'sanitize_text_field' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_max_events_per_day', 'absint' );
	}

	public function create_menu( string $base_file ): void {
		add_options_page(
			esc_html__( 'Simple Event Sheets Settings', 'simple-event-listing-feed-from-google-sheets' ), // Page Title
			esc_html__( 'Simple Event Sheets', 'simple-event-listing-feed-from-google-sheets' ),          // Menu Title
			'manage_options',                  // Capability
			self::PLUG_SLUG,                   // Menu Slug
			[ $this, 'draw_settings' ]         // Callback function to display the page content
		);
		add_filter( 'plugin_action_links_' . $base_file, [ $this, 'settings_link' ] );
	}

	public static function settings_link( array $links ): array {
		// Add a "Settings" link for the plugin in the WordPress admin
		$settings_url = admin_url( 'options-general.php?page=' . self::PLUG_SLUG );
		$links[]      = "<a href='{$settings_url}'>Settings</a>";
		return $links;
	}

	public function draw_settings(): void {
		?>
		<div class="wrap">
			<h2>Simple Event Sheets Settings</h2>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'simple_event_sheets_action', 'simple_event_sheets_nonce' ); ?>
				<?php settings_fields( 'simple-event-sheets-group' ); ?>
				<?php do_settings_sections( 'simple-event-sheets-group' ); ?>

				<h3>Google Sheets Configuration</h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Google Sheet ID</th>
						<td>
							<input type="text" size="45" name="simple_event_sheets_sheet_id" value="<?php echo esc_attr( get_option( 'simple_event_sheets_sheet_id' ) ); ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Google Sheet Name</th>
						<td>
							<input type="text" size="45" name="simple_event_sheets_sheet_name" value="<?php echo esc_attr( get_option( 'simple_event_sheets_sheet_name' ) ); ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Google API KEY</th>
						<td>
							<p>Make sure to set referrer restrictions. Domain for client side or IP for server side loading.</p>
							<input type="text" size="45" name="simple_event_sheets_google_api_key" value="<?php echo esc_attr( get_option( 'simple_event_sheets_google_api_key' ) ); ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Load Sheet Server Side</th>
						<td>
							<input type="checkbox" name="simple_event_sheets_checkbox" value="1" <?php checked( 1, esc_attr( get_option( 'simple_event_sheets_checkbox' ) ), true ); ?> />
							<label for="simple_event_sheets_checkbox">This will load the data server side and pass to the client. (Defaults to Client Side for faster loading)</label>
						</td>
					</tr>
				</table>

				<h3>Event Display Settings</h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Show past events</th>
						<td>
							<input type="checkbox" name="simple_event_sheets_passed_events_checkbox" value="1" <?php checked( 1, esc_attr( get_option( 'simple_event_sheets_passed_events_checkbox' ) ), true ); ?> />
							<label for="simple_event_sheets_passed_events_checkbox">This will show past events. (Defaults to not displaying them.)</label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Default View</th>
						<td>
							<?php $default_view = get_option( 'simple_event_sheets_default_view', 'list' ); ?>
							<select name="simple_event_sheets_default_view">
								<option value="list" <?php selected( $default_view, 'list' ); ?>>List View</option>
								<option value="calendar" <?php selected( $default_view, 'calendar' ); ?>>Calendar View</option>
							</select>
							<p class="description">Choose which view to display by default when no view parameter is specified in the shortcode.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Show View Toggle</th>
						<td>
							<input type="checkbox" name="simple_event_sheets_show_view_toggle" value="1" <?php checked( 1, esc_attr( get_option( 'simple_event_sheets_show_view_toggle', 1 ) ), true ); ?> />
							<label for="simple_event_sheets_show_view_toggle">Allow users to switch between List and Calendar views</label>
						</td>
					</tr>
				</table>

				<h3>Calendar View Settings</h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Calendar Start Day</th>
						<td>
							<?php $start_day = get_option( 'simple_event_sheets_calendar_start_day', 'sunday' ); ?>
							<select name="simple_event_sheets_calendar_start_day">
								<option value="sunday" <?php selected( $start_day, 'sunday' ); ?>>Sunday</option>
								<option value="monday" <?php selected( $start_day, 'monday' ); ?>>Monday</option>
							</select>
							<p class="description">Choose which day the calendar week should start with.</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Max Events Per Day</th>
						<td>
							<?php $max_events = get_option( 'simple_event_sheets_max_events_per_day', 3 ); ?>
							<input type="number" min="1" max="10" name="simple_event_sheets_max_events_per_day" value="<?php echo esc_attr( $max_events ); ?>" />
							<p class="description">Maximum number of events to display in each calendar day before showing "X more" link (1-10).</p>
						</td>
					</tr>
				</table>

				<h3>Shortcode Usage Examples</h3>
				<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #007cba; margin: 20px 0;">
					<h4>Basic Usage:</h4>
					<code>[simple_event_sheets]</code> - Uses default settings from above

					<h4>Advanced Usage:</h4>
					<code>[simple_event_sheets view="calendar"]</code> - Force calendar view<br>
					<code>[simple_event_sheets view="list" show_toggle="false"]</code> - List view only, no toggle<br>
					<code>[simple_event_sheets view="calendar" show_toggle="false"]</code> - Calendar view only, no toggle

					<h4>Available Parameters:</h4>
					<ul>
						<li><strong>view</strong>: "list" or "calendar" - Override default view</li>
						<li><strong>show_toggle</strong>: "true" or "false" - Override view toggle setting</li>
					</ul>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>

		<style>
			.form-table h3 {
				margin-top: 30px;
				margin-bottom: 10px;
				color: #23282d;
			}
			.form-table h3:first-child {
				margin-top: 0;
			}
		</style>
		<?php
	}
}
