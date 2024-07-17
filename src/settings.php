<?php

namespace SimpleEventSheets;

class Settings {

	private const PLUG_SLUG = 'simple-event-listing-feed-from-google-sheets';

	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'create_menu' ] );
	}

	public function register_settings(): void {
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_sheet_id', 'sanitize_text_field' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_sheet_name', 'sanitize_text_field' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_google_api_key', 'sanitize_text_field' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_checkbox' );
		register_setting( 'simple-event-sheets-group', 'simple_event_sheets_passed_events_checkbox' );
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
							<p>Make sure to set referrer restrictions. Domain for client side or IP for server side loading .</p>
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
					<tr valign="top">
						<th scope="row">Show past events</th>
						<td>
							<input type="checkbox" name="simple_event_sheets_passed_events_checkbox" value="1" <?php checked( 1, esc_attr( get_option( 'simple_event_sheets_passed_events_checkbox' ) ), true ); ?> />
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
