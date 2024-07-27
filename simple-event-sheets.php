<?php

/**
 * Plugin Name:       Simple Event Listing feed from Google Sheets
 * Plugin URI:        https://wordpress.org/plugins/simple-event-listing-feed-from-google-sheets/
 * Description:       A plugin that displays Event Listings.
 * Install:           Drop this directory in the "wp-content/plugins/" directory and activate it. You need to specify "[simple_event_sheets]" in the code section of a page or a post.
 * Contributors:      pjaudiomv
 * Author:            pjaudiomv
 * Author URI:        https://github.com/pjaudiomv/simple-event-sheets/
 * Version:           1.0.1
 * Requires PHP:      8.0
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace SimpleEventSheets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	function ( string $class ) {
		if ( str_starts_with( $class, 'SimpleEventSheets\\' ) ) {
			$class = str_replace( 'SimpleEventSheets\\', '', $class );
			require __DIR__ . '/src/' . str_replace( '\\', '/', $class ) . '.php';
		}
	}
);

class SimpleEventSheetsPlugin {

	private static ?self $instance = null;

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'options_menu' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'assets' ] );
		add_shortcode( 'simple_event_sheets', [ $this, 'events' ] );
	}

	public function options_menu(): void {
		$dashboard = new Settings();
		$dashboard->create_menu( plugin_basename( __FILE__ ) );
	}

	public function events( string|array $atts ): string {
		$event = new Events();
		return $event->render_events( $atts );
	}

	public function assets(): void {
		if ( ! is_admin() ) {
			$event = new Events();
			wp_enqueue_style( 'simple-event-sheets-css', plugin_dir_url( __FILE__ ) . 'src/assets/css/simple-event-sheets.css', false, filemtime( plugin_dir_path( __FILE__ ) . 'src/assets/css/simple-event-sheets.css' ), false );
			wp_enqueue_script( 'simple-event-sheets-js', plugin_dir_url( __FILE__ ) . 'src/assets/js/simple-event-sheets.js', [ 'jquery' ], '1.0', true );
			wp_localize_script(
				'simple-event-sheets-js',
				'simpleEventSheetsParams',
				[
					'SHEET_ID' => esc_js( get_option( 'simple_event_sheets_sheet_id' ) ),
					'SHEET_NAME' => esc_js( get_option( 'simple_event_sheets_sheet_name' ) ),
					'API_KEY' => esc_js( get_option( 'simple_event_sheets_google_api_key' ) ),
					'SHOW_PASSED_EVENTS' => esc_attr( get_option( 'simple_event_sheets_passed_events_checkbox' ) ),
					'EVENTS' => $event->optional_get_events(),
				]
			);
		}
	}

	public static function get_instance(): self {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

SimpleEventSheetsPlugin::get_instance();
